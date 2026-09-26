<?php
if (!defined('ABSPATH')) exit;

/* HCDecor production workflow engine: queue claim -> process -> review -> approve -> publish web. */

function hcdecor_workflow_log($job_id,$event,$note=''){
    $log=(array)get_post_meta($job_id,'hc_workflow_log',true);
    $log[]=[
        'time'=>current_time('mysql'),
        'event'=>sanitize_key($event),
        'note'=>sanitize_text_field($note),
        'user'=>get_current_user_id()
    ];
    if(count($log)>100) $log=array_slice($log,-100);
    update_post_meta($job_id,'hc_workflow_log',$log);
}

function hcdecor_workflow_set_status($job_id,$status,$note=''){
    $allowed=['draft','processing','review','approved','published_web','failed'];
    if(!in_array($status,$allowed,true)) return false;
    $old=(string)get_post_meta($job_id,'hc_agent_status',true);
    update_post_meta($job_id,'hc_agent_status',$status);
    hcdecor_workflow_log($job_id,$status,$note?:($old.' → '.$status));
    do_action('hcdecor_workflow_status_changed',$job_id,$old,$status);
    return true;
}

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/operations/claim',[
        'methods'=>'POST',
        'permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $now=time();
            $jobs=get_posts([
                'post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>20,
                'orderby'=>'date','order'=>'ASC',
                'meta_query'=>[['key'=>'hc_agent_status','value'=>'draft']]
            ]);
            foreach($jobs as $j){
                $lock=(int)get_post_meta($j->ID,'hc_agent_lock_until',true);
                if($lock>$now) continue;
                update_post_meta($j->ID,'hc_agent_lock_until',$now+600);
                update_post_meta($j->ID,'hc_agent_claimed_at',current_time('mysql'));
                hcdecor_workflow_set_status($j->ID,'processing','Agent claimed job');
                $data=[
                    'id'=>$j->ID,'title'=>$j->post_title,'brief'=>$j->post_content,
                    'project_id'=>(int)get_post_meta($j->ID,'hc_project_id',true),
                    'channels'=>(array)get_post_meta($j->ID,'hc_channels',true),
                    'media_ids'=>(array)get_post_meta($j->ID,'hc_media_ids',true),
                    'cover_id'=>(int)get_post_meta($j->ID,'hc_cover_id',true),
                    'status'=>'processing','outbound'=>false
                ];
                return rest_ensure_response($data);
            }
            return rest_ensure_response(['job'=>null,'message'=>'Queue empty']);
        }
    ]);

    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)/heartbeat',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $id=(int)$r['id'];
            if(get_post_type($id)!=='hc_content_job') return new WP_Error('not_found','Job not found',['status'=>404]);
            update_post_meta($id,'hc_agent_lock_until',time()+600);
            update_post_meta($id,'hc_agent_heartbeat',current_time('mysql'));
            return rest_ensure_response(['ok'=>true,'id'=>$id]);
        }
    ]);

    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)/complete',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $id=(int)$r['id'];
            if(get_post_type($id)!=='hc_content_job') return new WP_Error('not_found','Job not found',['status'=>404]);
            $p=$r->get_json_params()?:[];
            if(function_exists('hcdecor_ops_save_fields')) hcdecor_ops_save_fields($id,$p);
            delete_post_meta($id,'hc_agent_lock_until');
            hcdecor_workflow_set_status($id,'review','Agent completed generation');
            return rest_ensure_response(['ok'=>true,'id'=>$id,'status'=>'review','outbound'=>false]);
        }
    ]);

    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)/fail',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $id=(int)$r['id'];
            if(get_post_type($id)!=='hc_content_job') return new WP_Error('not_found','Job not found',['status'=>404]);
            $msg=sanitize_text_field((string)$r->get_param('message'));
            update_post_meta($id,'hc_agent_error',$msg);
            delete_post_meta($id,'hc_agent_lock_until');
            hcdecor_workflow_set_status($id,'failed',$msg?:'Agent failed');
            return rest_ensure_response(['ok'=>true,'id'=>$id,'status'=>'failed','outbound'=>false]);
        }
    ]);
});

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','Review Center','Review Center','edit_posts','hcdecor-review','hcdecor_review_page',2);
},22);

add_action('admin_post_hcdecor_review_action',function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    $id=(int)($_POST['job_id']??0);
    check_admin_referer('hcdecor_review_'.$id);
    if(get_post_type($id)!=='hc_content_job') wp_die('Invalid job');
    $action=sanitize_key($_POST['review_action']??'');
    if($action==='approve'){
        hcdecor_workflow_set_status($id,'approved','Approved by reviewer');
    }elseif($action==='approve_publish'){
        if(!current_user_can('publish_posts')) wp_die('Forbidden');
        hcdecor_workflow_set_status($id,'approved','Approved by reviewer');
        if(!function_exists('hcdecor_publish_job_to_web')) wp_die('Web publisher unavailable');
        $published=hcdecor_publish_job_to_web($id);
        if(is_wp_error($published)) wp_die($published->get_error_message());
        wp_safe_redirect(admin_url('admin.php?page=hcdecor-review&job='.$id.'&published=1')); exit;
    }elseif($action==='changes'){
        hcdecor_workflow_set_status($id,'draft','Returned for changes');
        update_post_meta($id,'hc_review_note',sanitize_textarea_field(wp_unslash($_POST['review_note']??'')));
    }
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-review&job='.$id.'&done=1')); exit;
});

function hcdecor_review_page(){
    if(!current_user_can('edit_posts')) return;
    $jobs=get_posts([
        'post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>50,
        'orderby'=>'modified','order'=>'DESC',
        'meta_query'=>[['key'=>'hc_agent_status','value'=>['review','approved','failed'],'compare'=>'IN']]
    ]);
    $job_id=(int)($_GET['job']??($jobs[0]->ID??0));
    $job=$job_id&&get_post_type($job_id)==='hc_content_job'?get_post($job_id):null;
    ?>
    <div class="wrap hcr"><style>
    .hcr{max-width:1450px}.hcr-head{display:flex;justify-content:space-between;align-items:center;margin:16px 0}.hcr-grid{display:grid;grid-template-columns:280px minmax(520px,1fr);gap:14px}.hcr-card{background:#fff;border:1px solid #dcdcde;border-radius:14px;overflow:hidden}.hcr-card h2{font-size:14px;margin:0;padding:14px 16px;border-bottom:1px solid #eee}.hcr-body{padding:14px}.hcr-job{display:block;text-decoration:none;color:#1d2327;border:1px solid #eee;border-radius:10px;padding:10px;margin-bottom:8px}.hcr-job.active{border-color:#c7863d;background:#fff9f2}.hcr-job small{display:block;color:#777}.hcr-preview{display:grid;grid-template-columns:1fr 1fr;gap:12px}.hcr-channel{border:1px solid #e4e4e4;border-radius:12px;padding:14px;background:#fafafa}.hcr-channel h3{margin:0 0 8px}.hcr-channel p{white-space:pre-wrap}.hcr-status{display:inline-block;padding:6px 9px;border-radius:999px;background:#17191c;color:#f2b66f;font-weight:700;font-size:12px}.hcr-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}.hcr textarea{width:100%;min-height:70px}@media(max-width:900px){.hcr-grid{grid-template-columns:1fr}.hcr-preview{grid-template-columns:1fr}}@media(max-width:600px){.hcr{margin-right:10px}.hcr-head{display:block}.hcr-actions{position:sticky;bottom:0;background:#fff;padding:10px 0;z-index:2}.hcr-actions .button{width:100%;min-height:44px;justify-content:center}.hcr textarea{font-size:16px;min-height:90px}.hcr-job{padding:13px}}
    </style>
    <div class="hcr-head"><div><h1>HCDecor Review Center</h1><p>Kiểm tra nội dung Agent trước khi publish.</p></div><span class="hcr-status">SOCIAL OUTBOUND OFF</span></div>
    <div class="hcr-grid"><aside class="hcr-card"><h2>REVIEW QUEUE</h2><div class="hcr-body">
    <?php foreach($jobs as $j): $s=(string)get_post_meta($j->ID,'hc_agent_status',true);?>
      <a class="hcr-job <?php echo $job_id===$j->ID?'active':'';?>" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-review&job='.$j->ID));?>"><strong><?php echo esc_html($j->post_title);?></strong><small><?php echo esc_html(strtoupper($s));?></small></a>
    <?php endforeach; if(!$jobs):?><p>Chưa có job cần review.</p><?php endif;?>
    </div></aside>
    <section class="hcr-card"><h2>CONTENT REVIEW</h2><div class="hcr-body">
    <?php if($job):
      $status=(string)get_post_meta($job_id,'hc_agent_status',true);
      $fields=[
        'Web'=>[(string)get_post_meta($job_id,'hc_web_title',true),(string)get_post_meta($job_id,'hc_web_intro',true)."

".(string)get_post_meta($job_id,'hc_web_body',true)],
        'Facebook'=>['',(string)get_post_meta($job_id,'hc_facebook_caption',true)],
        'TikTok / Reels'=>['',(string)get_post_meta($job_id,'hc_tiktok_script',true)],
        'YouTube'=>[(string)get_post_meta($job_id,'hc_youtube_title',true),(string)get_post_meta($job_id,'hc_youtube_description',true)]
      ];?>
      <p><span class="hcr-status"><?php echo esc_html(strtoupper($status));?></span></p>
      <div class="hcr-preview"><?php foreach($fields as $name=>$x):?><div class="hcr-channel"><h3><?php echo esc_html($name);?></h3><?php if($x[0]):?><strong><?php echo esc_html($x[0]);?></strong><?php endif;?><p><?php echo esc_html(trim($x[1]));?></p></div><?php endforeach;?></div>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><?php wp_nonce_field('hcdecor_review_'.$job_id);?><input type="hidden" name="action" value="hcdecor_review_action"><input type="hidden" name="job_id" value="<?php echo $job_id;?>">
        <label><strong>Ghi chú chỉnh sửa</strong></label><textarea name="review_note"></textarea>
        <div class="hcr-actions"><button class="button" name="review_action" value="changes">Trả về chỉnh sửa</button><?php if($status==='review'):?><button class="button" name="review_action" value="approve">Approve</button><?php if(current_user_can('publish_posts')):?><button class="button button-primary" name="review_action" value="approve_publish">Approve + Publish Web</button><?php endif;?><?php endif;?><a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-content-operations&job='.$job_id));?>">Mở Content Job</a></div>
      </form>
      <?php if($status==='approved'):?><p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-content-operations&job='.$job_id));?>">Publish Web trong Content Operations</a></p><?php endif;?>
    <?php else:?><p>Chưa có Content Job để review.</p><?php endif;?>
    </div></section></div></div><?php
}
