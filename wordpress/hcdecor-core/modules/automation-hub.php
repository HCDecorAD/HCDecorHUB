<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor Automation HUB
 * Patterns adopted from mature WordPress automation/social tools:
 * - trigger -> action recipes
 * - per-channel content queue
 * - scheduling + retry/backoff
 * - editorial status
 * - evergreen re-share
 * - outgoing webhooks
 *
 * External social publishing remains disabled until a connector is explicitly configured.
 */

function hcdecor_auto_settings(){
    $d=[
        'enabled'=>true,
        'social_enabled'=>false,
        'webhook_enabled'=>false,
        'webhook_url'=>'',
        'evergreen_enabled'=>false,
        'evergreen_days'=>30,
        'max_attempts'=>4,
        'retry_minutes'=>15
    ];
    $v=get_option('hcdecor_automation_settings',[]);
    return wp_parse_args(is_array($v)?$v:[],$d);
}

function hcdecor_auto_statuses(){
    return ['queued','scheduled','running','done','failed','blocked'];
}

add_action('init',function(){
    register_post_type('hc_automation_task',[
        'labels'=>['name'=>'Automation Tasks','singular_name'=>'Automation Task'],
        'public'=>false,'show_ui'=>false,'show_in_menu'=>false,
        'supports'=>['title','editor','custom-fields']
    ]);
},18);

function hcdecor_auto_enqueue($type,$payload=[],$run_at=null,$dedupe=''){
    $s=hcdecor_auto_settings();
    if(empty($s['enabled'])) return new WP_Error('disabled','Automation HUB is disabled.');
    $dedupe=sanitize_text_field((string)$dedupe);
    if($dedupe!==''){
        $existing=get_posts([
            'post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>1,
            'meta_query'=>[
                ['key'=>'hc_auto_dedupe','value'=>$dedupe],
                ['key'=>'hc_auto_status','value'=>['queued','scheduled','running'],'compare'=>'IN']
            ]
        ]);
        if($existing) return (int)$existing[0]->ID;
    }
    $id=wp_insert_post([
        'post_type'=>'hc_automation_task','post_status'=>'publish',
        'post_title'=>sanitize_text_field(strtoupper($type).' · '.current_time('Y-m-d H:i:s')),
        'post_content'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
    ]);
    if(is_wp_error($id)) return $id;
    update_post_meta($id,'hc_auto_type',sanitize_key($type));
    update_post_meta($id,'hc_auto_status',$run_at && $run_at>time()?'scheduled':'queued');
    update_post_meta($id,'hc_auto_run_at',(int)($run_at?:time()));
    update_post_meta($id,'hc_auto_attempts',0);
    update_post_meta($id,'hc_auto_dedupe',$dedupe);
    update_post_meta($id,'hc_auto_created_at',current_time('mysql'));
    return $id;
}

function hcdecor_auto_payload($task_id){
    $p=json_decode((string)get_post_field('post_content',$task_id),true);
    return is_array($p)?$p:[];
}

function hcdecor_auto_log($task_id,$event,$note=''){
    $log=(array)get_post_meta($task_id,'hc_auto_log',true);
    $log[]=['time'=>current_time('mysql'),'event'=>sanitize_key($event),'note'=>sanitize_text_field($note)];
    if(count($log)>50) $log=array_slice($log,-50);
    update_post_meta($task_id,'hc_auto_log',$log);
}

function hcdecor_auto_retry($task_id,$message){
    $s=hcdecor_auto_settings();
    $attempts=(int)get_post_meta($task_id,'hc_auto_attempts',true)+1;
    update_post_meta($task_id,'hc_auto_attempts',$attempts);
    update_post_meta($task_id,'hc_auto_last_error',sanitize_text_field($message));
    if($attempts >= max(1,(int)$s['max_attempts'])){
        update_post_meta($task_id,'hc_auto_status','failed');
        hcdecor_auto_log($task_id,'failed',$message);
        return false;
    }
    $delay=max(60,(int)$s['retry_minutes']*60) * max(1,$attempts);
    update_post_meta($task_id,'hc_auto_run_at',time()+$delay);
    update_post_meta($task_id,'hc_auto_status','scheduled');
    hcdecor_auto_log($task_id,'retry','Attempt '.$attempts.' · '.$message);
    return true;
}

function hcdecor_auto_webhook($event,$payload){
    $s=hcdecor_auto_settings();
    if(empty($s['webhook_enabled']) || empty($s['webhook_url'])) return new WP_Error('blocked','Webhook disabled.');
    $url=esc_url_raw($s['webhook_url']);
    if(!$url || !wp_http_validate_url($url)) return new WP_Error('url','Invalid webhook URL.');
    $body=['event'=>$event,'site'=>home_url('/'),'time'=>current_time('mysql'),'payload'=>$payload];
    $r=wp_remote_post($url,[
        'timeout'=>20,
        'headers'=>['Content-Type'=>'application/json','X-HCDecor-Event'=>$event],
        'body'=>wp_json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
    ]);
    if(is_wp_error($r)) return $r;
    $code=(int)wp_remote_retrieve_response_code($r);
    if($code<200 || $code>=300) return new WP_Error('webhook_http','Webhook HTTP '.$code);
    return ['ok'=>true,'code'=>$code];
}

function hcdecor_auto_prepare_social($project_id,$job_id=0){
    $project=get_post($project_id);
    if(!$project || $project->post_type!=='hc_project') return new WP_Error('project','Invalid project.');
    $channels=['facebook','tiktok','youtube'];
    $payload=[
        'project_id'=>(int)$project_id,
        'job_id'=>(int)$job_id,
        'url'=>get_permalink($project_id),
        'featured'=>get_the_post_thumbnail_url($project_id,'large')?:'',
        'channels'=>[]
    ];
    foreach($channels as $ch){
        $text='';
        if($job_id){
            $key=$ch==='facebook'?'hc_facebook_caption':($ch==='tiktok'?'hc_tiktok_script':'hc_youtube_description');
            $text=(string)get_post_meta($job_id,$key,true);
        }
        $payload['channels'][$ch]=['text'=>$text,'status'=>'ready'];
    }
    return $payload;
}

function hcdecor_auto_run_task($task_id){
    $type=(string)get_post_meta($task_id,'hc_auto_type',true);
    $payload=hcdecor_auto_payload($task_id);
    update_post_meta($task_id,'hc_auto_status','running');
    update_post_meta($task_id,'hc_auto_started_at',current_time('mysql'));
    hcdecor_auto_log($task_id,'running',$type);

    if($type==='webhook'){
        $r=hcdecor_auto_webhook((string)($payload['event']??'hcdecor.event'),(array)($payload['data']??[]));
        if(is_wp_error($r)) return hcdecor_auto_retry($task_id,$r->get_error_message());
    }
    elseif($type==='social_publish'){
        $s=hcdecor_auto_settings();
        if(empty($s['social_enabled'])){
            update_post_meta($task_id,'hc_auto_status','blocked');
            hcdecor_auto_log($task_id,'blocked','Social outbound is OFF');
            return false;
        }
        // Connector adapter is intentionally separate. No direct social API call until credentials are connected.
        $r=hcdecor_auto_webhook('hcdecor.social_publish',$payload);
        if(is_wp_error($r)) return hcdecor_auto_retry($task_id,$r->get_error_message());
    }
    elseif($type==='evergreen'){
        $project=(int)($payload['project_id']??0);
        $prepared=hcdecor_auto_prepare_social($project,0);
        if(is_wp_error($prepared)) return hcdecor_auto_retry($task_id,$prepared->get_error_message());
        $next=hcdecor_auto_enqueue('social_publish',$prepared,time(),'social:evergreen:'.$project.':'.gmdate('Ymd'));
        if(is_wp_error($next)) return hcdecor_auto_retry($task_id,$next->get_error_message());
    }
    else{
        update_post_meta($task_id,'hc_auto_status','failed');
        hcdecor_auto_log($task_id,'failed','Unknown automation type');
        return false;
    }

    update_post_meta($task_id,'hc_auto_status','done');
    update_post_meta($task_id,'hc_auto_done_at',current_time('mysql'));
    delete_post_meta($task_id,'hc_auto_last_error');
    hcdecor_auto_log($task_id,'done',$type);
    return true;
}

add_filter('cron_schedules',function($s){
    if(!isset($s['hcdecor_1min'])) $s['hcdecor_1min']=['interval'=>60,'display'=>'HCDecor every minute'];
    if(!isset($s['hcdecor_daily'])) $s['hcdecor_daily']=['interval'=>DAY_IN_SECONDS,'display'=>'HCDecor daily'];
    return $s;
});
add_action('init',function(){
    if(!wp_next_scheduled('hcdecor_automation_tick')) wp_schedule_event(time()+20,'hcdecor_1min','hcdecor_automation_tick');
    if(!wp_next_scheduled('hcdecor_evergreen_tick')) wp_schedule_event(time()+300,'hcdecor_daily','hcdecor_evergreen_tick');
},50);

add_action('hcdecor_automation_tick',function(){
    $tasks=get_posts([
        'post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>5,'orderby'=>'date','order'=>'ASC',
        'meta_query'=>[
            'relation'=>'AND',
            ['key'=>'hc_auto_status','value'=>['queued','scheduled'],'compare'=>'IN'],
            ['key'=>'hc_auto_run_at','value'=>time(),'compare'=>'<=','type'=>'NUMERIC']
        ]
    ]);
    foreach($tasks as $t) hcdecor_auto_run_task($t->ID);
});

add_action('hcdecor_evergreen_tick',function(){
    $s=hcdecor_auto_settings();
    if(empty($s['evergreen_enabled'])) return;
    $days=max(7,(int)$s['evergreen_days']);
    $before=date('Y-m-d H:i:s',time()-$days*DAY_IN_SECONDS);
    $projects=get_posts([
        'post_type'=>'hc_project','post_status'=>'publish','numberposts'=>3,
        'date_query'=>[['before'=>$before]],
        'orderby'=>'rand'
    ]);
    foreach($projects as $p){
        hcdecor_auto_enqueue('evergreen',['project_id'=>$p->ID],time(),'evergreen:'.$p->ID.':'.gmdate('Ymd'));
    }
});

add_action('hcdecor_after_web_publish',function($job_id,$project_id){
    $prepared=hcdecor_auto_prepare_social($project_id,$job_id);
    if(!is_wp_error($prepared)) hcdecor_auto_enqueue('social_publish',$prepared,time()+60,'social:publish:'.$job_id);
    hcdecor_auto_enqueue('webhook',['event'=>'hcdecor.web_published','data'=>['job_id'=>$job_id,'project_id'=>$project_id,'url'=>get_permalink($project_id)]],time(),'webhook:web:'.$job_id);
},10,2);

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','Automation HUB','Automation HUB','manage_options','hcdecor-automation','hcdecor_automation_page',5);
},28);

add_action('admin_post_hcdecor_automation_settings',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_automation_settings');
    $old=hcdecor_auto_settings();
    $new=[
        'enabled'=>!empty($_POST['enabled']),
        'social_enabled'=>!empty($_POST['social_enabled']),
        'webhook_enabled'=>!empty($_POST['webhook_enabled']),
        'webhook_url'=>esc_url_raw(wp_unslash($_POST['webhook_url']??'')),
        'evergreen_enabled'=>!empty($_POST['evergreen_enabled']),
        'evergreen_days'=>max(7,(int)($_POST['evergreen_days']??30)),
        'max_attempts'=>max(1,min(10,(int)($_POST['max_attempts']??4))),
        'retry_minutes'=>max(1,min(1440,(int)($_POST['retry_minutes']??15)))
    ];
    // Do not allow social outbound without a configured webhook connector.
    if($new['social_enabled'] && (!$new['webhook_enabled'] || !$new['webhook_url'])) $new['social_enabled']=false;
    update_option('hcdecor_automation_settings',$new,false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-automation&saved=1')); exit;
});

add_action('admin_post_hcdecor_automation_retry',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    $id=(int)($_POST['task_id']??0); check_admin_referer('hcdecor_automation_retry_'.$id);
    if(get_post_type($id)!=='hc_automation_task') wp_die('Invalid task');
    update_post_meta($id,'hc_auto_status','queued'); update_post_meta($id,'hc_auto_run_at',time());
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-automation&retried=1')); exit;
});

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/automation/status',[
        'methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(){
            $counts=[]; foreach(hcdecor_auto_statuses() as $s){$q=new WP_Query(['post_type'=>'hc_automation_task','post_status'=>'publish','posts_per_page'=>1,'meta_key'=>'hc_auto_status','meta_value'=>$s]);$counts[$s]=(int)$q->found_posts;}
            return rest_ensure_response(['settings'=>hcdecor_auto_settings(),'queue'=>$counts,'outbound'=>(bool)hcdecor_auto_settings()['social_enabled']]);
        }
    ]);
    register_rest_route('hcdecor/v1','/automation/queue',[
        'methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(){
            $tasks=get_posts(['post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>50,'orderby'=>'date','order'=>'DESC']);
            return rest_ensure_response(array_map(function($t){return [
                'id'=>$t->ID,'type'=>(string)get_post_meta($t->ID,'hc_auto_type',true),
                'status'=>(string)get_post_meta($t->ID,'hc_auto_status',true),
                'run_at'=>(int)get_post_meta($t->ID,'hc_auto_run_at',true),
                'attempts'=>(int)get_post_meta($t->ID,'hc_auto_attempts',true),
                'error'=>(string)get_post_meta($t->ID,'hc_auto_last_error',true)
            ];},$tasks));
        }
    ]);
});

function hcdecor_automation_page(){
    if(!current_user_can('manage_options')) return;
    $s=hcdecor_auto_settings();
    $tasks=get_posts(['post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>40,'orderby'=>'date','order'=>'DESC']);
    ?>
    <div class="wrap hca" style="max-width:1300px"><h1>HCDecor Automation HUB</h1>
    <p>Trigger → Queue → Action → Retry → Review/Publish. Social outbound chỉ bật khi có connector.</p>
    <?php if(isset($_GET['saved'])):?><div class="notice notice-success inline"><p>Đã lưu Automation HUB.</p></div><?php endif;?>
    <div style="display:grid;grid-template-columns:430px 1fr;gap:14px">
      <section style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:18px">
      <h2>Automation Settings</h2>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
        <input type="hidden" name="action" value="hcdecor_automation_settings"><?php wp_nonce_field('hcdecor_automation_settings');?>
        <p><label><input type="checkbox" name="enabled" <?php checked($s['enabled']);?>> Master automation</label></p>
        <p><label><input type="checkbox" name="webhook_enabled" <?php checked($s['webhook_enabled']);?>> Outgoing Webhook connector</label></p>
        <p><input style="width:100%" type="url" name="webhook_url" value="<?php echo esc_attr($s['webhook_url']);?>" placeholder="https://.../webhook"></p>
        <p><label><input type="checkbox" name="social_enabled" <?php checked($s['social_enabled']);?>> Social publishing</label><br><small>Chỉ bật khi webhook connector đã cấu hình.</small></p>
        <p><label><input type="checkbox" name="evergreen_enabled" <?php checked($s['evergreen_enabled']);?>> Evergreen / revive old projects</label></p>
        <p>Evergreen sau <input type="number" name="evergreen_days" value="<?php echo (int)$s['evergreen_days'];?>" min="7" style="width:80px"> ngày</p>
        <p>Retry <input type="number" name="max_attempts" value="<?php echo (int)$s['max_attempts'];?>" min="1" max="10" style="width:70px"> lần · mỗi <input type="number" name="retry_minutes" value="<?php echo (int)$s['retry_minutes'];?>" min="1" style="width:80px"> phút</p>
        <p><button class="button button-primary">Lưu Automation</button></p>
      </form>
      <hr><p><strong>Patterns:</strong> Editorial Calendar · per-channel queue · evergreen re-share · trigger/action recipes · webhooks · retry/backoff.</p>
      </section>
      <section style="background:#fff;border:1px solid #ddd;border-radius:12px;overflow:hidden">
      <h2 style="padding:14px 16px;margin:0;border-bottom:1px solid #eee">Automation Queue</h2>
      <table class="widefat striped"><thead><tr><th>ID</th><th>Type</th><th>Status</th><th>Run</th><th>Attempts</th><th>Error</th><th></th></tr></thead><tbody>
      <?php foreach($tasks as $t): $status=(string)get_post_meta($t->ID,'hc_auto_status',true);?>
      <tr><td><?php echo $t->ID;?></td><td><?php echo esc_html(get_post_meta($t->ID,'hc_auto_type',true));?></td><td><strong><?php echo esc_html(strtoupper($status));?></strong></td><td><?php $run=(int)get_post_meta($t->ID,'hc_auto_run_at',true);echo $run?esc_html(wp_date('d/m H:i',$run)):'';?></td><td><?php echo (int)get_post_meta($t->ID,'hc_auto_attempts',true);?></td><td><?php echo esc_html(get_post_meta($t->ID,'hc_auto_last_error',true));?></td><td><?php if(in_array($status,['failed','blocked'],true)):?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="hcdecor_automation_retry"><input type="hidden" name="task_id" value="<?php echo $t->ID;?>"><?php wp_nonce_field('hcdecor_automation_retry_'.$t->ID);?><button class="button">Retry</button></form><?php endif;?></td></tr>
      <?php endforeach; if(!$tasks):?><tr><td colspan="7">Queue trống.</td></tr><?php endif;?>
      </tbody></table></section>
    </div></div><?php
}
