<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor Automation Recipes + Connector Layer
 * Trigger -> Conditions -> Actions
 */

function hcdecor_connector_registry(){
    return apply_filters('hcdecor_connector_registry',[
        'wordpress'=>[
            'label'=>'WordPress',
            'status'=>'connected',
            'actions'=>['publish_project','update_project','create_media_meta']
        ],
        'webhook'=>[
            'label'=>'Webhook',
            'status'=>hcdecor_auto_settings()['webhook_enabled']?'connected':'not_configured',
            'actions'=>['send_webhook']
        ],
        'facebook'=>[
            'label'=>'Facebook',
            'status'=>'not_configured',
            'actions'=>['publish_post','publish_reel']
        ],
        'tiktok'=>[
            'label'=>'TikTok',
            'status'=>'not_configured',
            'actions'=>['publish_video']
        ],
        'youtube'=>[
            'label'=>'YouTube',
            'status'=>'not_configured',
            'actions'=>['publish_video','update_description']
        ],
        'drive'=>[
            'label'=>'Google Drive Vault',
            'status'=>function_exists('hcdecor_drive_configured')&&hcdecor_drive_configured()?'connected':'not_configured',
            'actions'=>['save_job','load_job','archive_media','save_prompt','export_package']
        ]
    ]);
}

function hcdecor_recipe_defaults(){
    return [
        [
            'id'=>'project_to_ai',
            'name'=>'Project + Media → HUB Agent',
            'enabled'=>true,
            'trigger'=>'project_media_ready',
            'conditions'=>[['field'=>'media_count','op'=>'>','value'=>0]],
            'actions'=>[['type'=>'create_content_job'],['type'=>'run_ai']]
        ],
        [
            'id'=>'approved_to_web',
            'name'=>'Approved → Publish Web',
            'enabled'=>true,
            'trigger'=>'content_approved',
            'conditions'=>[],
            'actions'=>[['type'=>'publish_web']]
        ],
        [
            'id'=>'review_to_drive',
            'name'=>'AI Review → Save Drive Vault',
            'enabled'=>true,
            'trigger'=>'content_review',
            'conditions'=>[],
            'actions'=>[['type'=>'save_drive']]
        ],
        [
            'id'=>'web_to_social_queue',
            'name'=>'Web Published → Social Queue',
            'enabled'=>true,
            'trigger'=>'web_published',
            'conditions'=>[],
            'actions'=>[['type'=>'enqueue_social']]
        ],
        [
            'id'=>'evergreen',
            'name'=>'Evergreen Project → Re-share',
            'enabled'=>false,
            'trigger'=>'daily',
            'conditions'=>[['field'=>'age_days','op'=>'>=','value'=>30]],
            'actions'=>[['type'=>'enqueue_social']]
        ]
    ];
}

function hcdecor_recipes(){
    $saved=get_option('hcdecor_automation_recipes',[]);
    return is_array($saved)&&$saved?$saved:hcdecor_recipe_defaults();
}

function hcdecor_recipe_condition_match($condition,$context){
    $field=(string)($condition['field']??'');
    $op=(string)($condition['op']??'==');
    $want=$condition['value']??null;
    $got=$context[$field]??null;
    switch($op){
        case '>': return (float)$got>(float)$want;
        case '>=': return (float)$got>=(float)$want;
        case '<': return (float)$got<(float)$want;
        case '<=': return (float)$got<=(float)$want;
        case '!=': return $got!=$want;
        case 'in': return in_array($got,(array)$want,true);
        default: return $got==$want;
    }
}

function hcdecor_recipe_matches($recipe,$trigger,$context){
    if(empty($recipe['enabled']) || ($recipe['trigger']??'')!==$trigger) return false;
    foreach((array)($recipe['conditions']??[]) as $condition){
        if(!hcdecor_recipe_condition_match($condition,$context)) return false;
    }
    return true;
}

function hcdecor_recipe_execute_action($action,$context){
    $type=(string)($action['type']??'');
    if($type==='create_content_job'){
        if(!function_exists('hcdecor_agent_create_job')) return new WP_Error('agent','Agent intake unavailable');
        return hcdecor_agent_create_job(
            (int)($context['project_id']??0),
            (array)($context['media_ids']??[]),
            (string)($context['brief']??'')
        );
    }
    if($type==='run_ai'){
        $job=(int)($context['job_id']??0);
        if(!$job || !function_exists('hcdecor_ai_generate_job')) return new WP_Error('ai','AI unavailable');
        return hcdecor_ai_generate_job($job);
    }
    if($type==='publish_web'){
        $job=(int)($context['job_id']??0);
        if(!$job || !function_exists('hcdecor_publish_job_to_web')) return new WP_Error('publish','Web publisher unavailable');
        return hcdecor_publish_job_to_web($job);
    }
    if($type==='enqueue_social'){
        $project=(int)($context['project_id']??0);
        $job=(int)($context['job_id']??0);
        if(!function_exists('hcdecor_auto_prepare_social') || !function_exists('hcdecor_auto_enqueue')) return new WP_Error('automation','Automation queue unavailable');
        $prepared=hcdecor_auto_prepare_social($project,$job);
        if(is_wp_error($prepared)) return $prepared;
        return hcdecor_auto_enqueue('social_publish',$prepared,time(),'recipe:social:'.$project.':'.$job);
    }
    if($type==='save_drive'){
        $job=(int)($context['job_id']??0);
        if(!$job || !function_exists('hcdecor_drive_save_job')) return new WP_Error('drive','Drive Vault unavailable');
        return hcdecor_drive_save_job($job,true);
    }
    if($type==='send_webhook'){
        if(!function_exists('hcdecor_auto_enqueue')) return new WP_Error('automation','Automation queue unavailable');
        return hcdecor_auto_enqueue('webhook',[
            'event'=>(string)($action['event']??'hcdecor.recipe'),
            'data'=>$context
        ],time());
    }
    return new WP_Error('action','Unknown recipe action: '.$type);
}

function hcdecor_recipe_fire($trigger,$context=[]){
    $runs=[];
    foreach(hcdecor_recipes() as $recipe){
        if(!hcdecor_recipe_matches($recipe,$trigger,$context)) continue;
        $ctx=$context; $ok=true; $results=[];
        foreach((array)($recipe['actions']??[]) as $action){
            $res=hcdecor_recipe_execute_action($action,$ctx);
            $results[]=$res;
            if(is_wp_error($res)){ $ok=false; break; }
            if(($action['type']??'')==='create_content_job' && is_numeric($res)) $ctx['job_id']=(int)$res;
        }
        $runs[]=['recipe'=>$recipe['id']??'','ok'=>$ok,'results'=>$results];
    }
    return $runs;
}

add_action('hcdecor_recipe_fire',function($trigger,$context=[]){
    hcdecor_recipe_fire($trigger,(array)$context);
},10,2);

add_action('hcdecor_after_web_publish',function($job_id,$project_id){
    do_action('hcdecor_recipe_fire','web_published',[
        'job_id'=>(int)$job_id,
        'project_id'=>(int)$project_id
    ]);
},20,2);

add_action('admin_post_hcdecor_recipe_toggle',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    $id=sanitize_key($_POST['recipe_id']??'');
    check_admin_referer('hcdecor_recipe_toggle_'.$id);
    $recipes=hcdecor_recipes();
    foreach($recipes as &$recipe){
        if(($recipe['id']??'')===$id) $recipe['enabled']=empty($recipe['enabled']);
    }
    unset($recipe);
    update_option('hcdecor_automation_recipes',$recipes,false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-automation&recipe=1')); exit;
});

add_action('admin_footer',function(){
    if(($_GET['page']??'')!=='hcdecor-automation') return;
    $recipes=hcdecor_recipes();
    $connectors=hcdecor_connector_registry(); ?>
    <style>
      .hc-auto-architecture{max-width:1300px;margin:18px 0;display:grid;grid-template-columns:1.15fr .85fr;gap:14px}
      .hc-auto-box{background:#fff;border:1px solid #ddd;border-radius:12px;padding:16px}
      .hc-recipe{border:1px solid #eee;border-radius:10px;padding:12px;margin:8px 0}
      .hc-recipe-flow{font-size:12px;color:#646970;margin-top:4px}
      .hc-connector-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}
      .hc-connector{border:1px solid #eee;border-radius:10px;padding:10px}
      .hc-connector em{font-style:normal;font-size:11px;padding:3px 7px;border-radius:999px;background:#f0f0f1}
      .hc-connector em.connected{background:#dff3e4;color:#176c2f}
      @media(max-width:900px){.hc-auto-architecture{grid-template-columns:1fr}}
    </style>
    <div class="hc-auto-architecture">
      <section class="hc-auto-box"><h2>Recipe Engine</h2>
      <?php foreach($recipes as $r):?>
        <div class="hc-recipe">
          <strong><?php echo esc_html($r['name']);?></strong>
          <div class="hc-recipe-flow"><?php echo esc_html($r['trigger']);?> → <?php echo esc_html(implode(' → ',array_map(function($a){return $a['type']??'';},(array)$r['actions'])));?></div>
          <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" style="margin-top:8px">
            <input type="hidden" name="action" value="hcdecor_recipe_toggle"><input type="hidden" name="recipe_id" value="<?php echo esc_attr($r['id']);?>">
            <?php wp_nonce_field('hcdecor_recipe_toggle_'.$r['id']);?>
            <button class="button"><?php echo !empty($r['enabled'])?'Disable':'Enable';?></button>
          </form>
        </div>
      <?php endforeach;?>
      </section>
      <section class="hc-auto-box"><h2>Connector Layer</h2><div class="hc-connector-grid">
      <?php foreach($connectors as $id=>$x):?>
        <div class="hc-connector"><strong><?php echo esc_html($x['label']);?></strong><br><em class="<?php echo $x['status']==='connected'?'connected':'';?>"><?php echo esc_html(strtoupper($x['status']));?></em><div style="margin-top:6px;color:#646970;font-size:12px"><?php echo esc_html(implode(', ',$x['actions']));?></div></div>
      <?php endforeach;?>
      </div></section>
    </div><?php
},50);


add_action('hcdecor_workflow_status_changed',function($job_id,$old,$status){
    if($status==='review'){
        do_action('hcdecor_recipe_fire','content_review',[
            'job_id'=>(int)$job_id,
            'project_id'=>(int)get_post_meta($job_id,'hc_project_id',true),
            'status'=>$status
        ]);
    }
},35,3);
