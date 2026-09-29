<?php
if (!defined('ABSPATH')) exit;

/* HCDecor guarded Web publisher with snapshot + rollback. */

function hcdecor_publish_snapshot($project_id){
    $post=get_post($project_id);
    if(!$post || $post->post_type!=='hc_project') return new WP_Error('project','Invalid project');
    return [
        'time'=>current_time('mysql'),
        'title'=>$post->post_title,
        'excerpt'=>$post->post_excerpt,
        'content'=>$post->post_content,
        'gallery'=>(array)get_post_meta($project_id,'hc_project_gallery',true),
        'gallery_legacy'=>(array)get_post_meta($project_id,'hc_gallery_ids',true),
        'cover'=>(int)get_post_thumbnail_id($project_id),
        'seo_meta'=>(string)get_post_meta($project_id,'hc_seo_meta',true)
    ];
}

function hcdecor_restore_project_snapshot($project_id,$snapshot){
    $project_id=(int)$project_id;
    if(!$project_id || get_post_type($project_id)!=='hc_project' || !is_array($snapshot)) return new WP_Error('snapshot','Invalid publish snapshot.');
    $r=wp_update_post(['ID'=>$project_id,'post_title'=>(string)($snapshot['title']??''),'post_excerpt'=>(string)($snapshot['excerpt']??''),'post_content'=>(string)($snapshot['content']??'')],true);
    if(is_wp_error($r)) return $r;
    $gallery=array_slice(array_values(array_filter(array_unique(array_map('intval',(array)($snapshot['gallery']??[]))),function($id){return get_post_type($id)==='attachment';})),0,60);
    $legacy=array_slice(array_values(array_filter(array_unique(array_map('intval',(array)($snapshot['gallery_legacy']??[]))),function($id){return get_post_type($id)==='attachment';})),0,60);
    update_post_meta($project_id,'hc_project_gallery',$gallery);
    update_post_meta($project_id,'hc_gallery_ids',$legacy);
    update_post_meta($project_id,'hc_seo_meta',(string)($snapshot['seo_meta']??''));
    $cover=(int)($snapshot['cover']??0);
    if($cover && wp_attachment_is_image($cover)) set_post_thumbnail($project_id,$cover);
    else delete_post_thumbnail($project_id);
    return true;
}

function hcdecor_publish_preflight($job_id){
    $job_id=(int)$job_id;
    if(get_post_type($job_id)!=='hc_content_job') return new WP_Error('job','Invalid content job');
    if(!function_exists('hcdecor_workflow_set_status')) return new WP_Error('workflow','Workflow engine unavailable.');
    if((string)get_post_meta($job_id,'hc_agent_status',true)!=='approved') return new WP_Error('status','Job must be approved first.');
    if(!(int)get_post_meta($job_id,'hc_reviewed_by',true) || !(string)get_post_meta($job_id,'hc_reviewed_at',true)) return new WP_Error('review','Reviewer audit is required before publish.');
    $approval_by=(int)get_post_meta($job_id,'hc_publish_approved_by',true);
    $approval_at=(string)get_post_meta($job_id,'hc_publish_approved_at',true);
    $approval_ts=$approval_at!==''?(strtotime($approval_at)?:0):0;
    if(!$approval_by || !$approval_ts) return new WP_Error('approval','Explicit production publish approval is required.');
    if($approval_ts<time()-15*MINUTE_IN_SECONDS){
        delete_post_meta($job_id,'hc_publish_approved_by');
        delete_post_meta($job_id,'hc_publish_approved_at');
        return new WP_Error('approval_expired','Production publish approval expired; approve again.');
    }
    $project=(int)get_post_meta($job_id,'hc_project_id',true);
    if(!$project || get_post_type($project)!=='hc_project') return new WP_Error('project','Invalid project');
    $title=trim((string)get_post_meta($job_id,'hc_web_title',true));
    $body=trim((string)get_post_meta($job_id,'hc_web_body',true));
    if($title==='') return new WP_Error('title','Web title is required.');
    if($body==='') return new WP_Error('body','Web body is required.');
    $media=array_values(array_unique(array_filter(array_map('intval',(array)get_post_meta($job_id,'hc_media_ids',true)))));
    $media=array_values(array_filter($media,function($id){return get_post_type($id)==='attachment';}));
    $cover=(int)get_post_meta($job_id,'hc_cover_id',true);
    if($cover && (!in_array($cover,$media,true) || !wp_attachment_is_image($cover))) return new WP_Error('cover','Cover must be an image in selected media.');
    return ['project_id'=>$project,'media_ids'=>$media,'cover_id'=>$cover];
}

function hcdecor_publish_job_to_web($job_id){
    $job_id=(int)$job_id;
    $approval_at=(string)get_post_meta($job_id,'hc_publish_approved_at',true);
    $approval_ts=$approval_at!==''?(strtotime($approval_at)?:0):0;
    if(!$approval_ts || $approval_ts<(time()-15*MINUTE_IN_SECONDS) || $approval_ts>(time()+5*MINUTE_IN_SECONDS)) return new WP_Error('approval','Fresh explicit production publish approval is required.');
    $preflight=hcdecor_publish_preflight($job_id);
    if(is_wp_error($preflight)) return $preflight;
    $project=(int)$preflight['project_id'];

    $snapshot=hcdecor_publish_snapshot($project);
    if(is_wp_error($snapshot)) return $snapshot;

    $title=(string)get_post_meta($job_id,'hc_web_title',true);
    $intro=(string)get_post_meta($job_id,'hc_web_intro',true);
    $body=(string)get_post_meta($job_id,'hc_web_body',true);
    $media=(array)$preflight['media_ids'];
    $cover=(int)$preflight['cover_id'];
    $seo=(string)get_post_meta($job_id,'hc_seo_meta',true);

    $result=wp_update_post([
        'ID'=>$project,
        'post_title'=>$title!==''?$title:get_the_title($project),
        'post_excerpt'=>$intro,
        'post_content'=>$body
    ],true);
    if(is_wp_error($result)) return $result;

    update_post_meta($project,'hc_project_gallery',$media);
    update_post_meta($project,'hc_gallery_ids',$media);
    update_post_meta($project,'hc_seo_meta',$seo);
    if($cover && wp_attachment_is_image($cover)) set_post_thumbnail($project,$cover);

    $approval_by=(int)get_post_meta($job_id,'hc_publish_approved_by',true);
    $approval_at=(string)get_post_meta($job_id,'hc_publish_approved_at',true);
    $history=(array)get_post_meta($job_id,'hc_publish_history',true);
    $history[]=['published_at'=>current_time('mysql'),'approved_by'=>$approval_by,'approved_at'=>$approval_at,'snapshot'=>$snapshot];
    if(count($history)>5) $history=array_slice($history,-5);
    update_post_meta($job_id,'hc_publish_history',$history);
    update_post_meta($job_id,'hc_publish_snapshot',$snapshot);
    update_post_meta($job_id,'hc_published_project_id',$project);
    update_post_meta($job_id,'hc_published_web_at',current_time('mysql'));
    update_post_meta($job_id,'hc_published_web_by',get_current_user_id());
    update_post_meta($job_id,'hc_published_web_url',get_permalink($project));
    update_post_meta($job_id,'hc_outbound',false);
    if(!hcdecor_workflow_set_status($job_id,'published_web','Published to Web')){
        hcdecor_restore_project_snapshot($project,$snapshot);
        delete_post_meta($job_id,'hc_published_project_id');
        delete_post_meta($job_id,'hc_published_web_at');
        delete_post_meta($job_id,'hc_published_web_by');
        delete_post_meta($job_id,'hc_published_web_url');
        return new WP_Error('transition','Web publish status transition was rejected; Project snapshot was restored.');
    }
    update_post_meta($job_id,'hc_last_publish_approved_by',$approval_by);
    update_post_meta($job_id,'hc_last_publish_approved_at',$approval_at);
    delete_post_meta($job_id,'hc_publish_approved_by');
    delete_post_meta($job_id,'hc_publish_approved_at');
    do_action('hcdecor_after_web_publish',$job_id,$project);

    return ['job_id'=>$job_id,'project_id'=>$project,'url'=>get_permalink($project)];
}

function hcdecor_rollback_job_publish($job_id){
    $job_id=(int)$job_id;
    if(!function_exists('hcdecor_workflow_set_status')) return new WP_Error('workflow','Workflow engine unavailable.');
    if((string)get_post_meta($job_id,'hc_agent_status',true)!=='published_web') return new WP_Error('status','Only published Web jobs can be rolled back.');
    $snapshot=get_post_meta($job_id,'hc_publish_snapshot',true);
    $project=(int)get_post_meta($job_id,'hc_published_project_id',true);
    if(!$project || get_post_type($project)!=='hc_project' || !is_array($snapshot)) return new WP_Error('snapshot','No rollback snapshot.');

    $r=wp_update_post([
        'ID'=>$project,
        'post_title'=>(string)($snapshot['title']??''),
        'post_excerpt'=>(string)($snapshot['excerpt']??''),
        'post_content'=>(string)($snapshot['content']??'')
    ],true);
    if(is_wp_error($r)) return $r;

    $gallery=array_slice(array_values(array_filter(array_unique(array_map('intval',(array)($snapshot['gallery']??[]))),function($id){return get_post_type($id)==='attachment';})),0,60);
    $legacy=array_slice(array_values(array_filter(array_unique(array_map('intval',(array)($snapshot['gallery_legacy']??[]))),function($id){return get_post_type($id)==='attachment';})),0,60);
    update_post_meta($project,'hc_project_gallery',$gallery);
    update_post_meta($project,'hc_gallery_ids',$legacy);
    update_post_meta($project,'hc_seo_meta',(string)($snapshot['seo_meta']??''));
    $cover=(int)($snapshot['cover']??0);
    if($cover && wp_attachment_is_image($cover)) set_post_thumbnail($project,$cover);
    else delete_post_thumbnail($project);

    if(!hcdecor_workflow_set_status($job_id,'approved','Rolled back Web publish')) return new WP_Error('transition','Web rollback status transition was rejected.');
    update_post_meta($job_id,'hc_rollback_at',current_time('mysql'));
    update_post_meta($job_id,'hc_rollback_by',get_current_user_id());
    update_post_meta($job_id,'hc_outbound',false);
    do_action('hcdecor_project_data_changed',$project);
    return ['job_id'=>$job_id,'project_id'=>$project,'url'=>get_permalink($project)];
}

add_action('admin_post_hcdecor_publish_rollback',function(){
    if(!current_user_can('publish_posts')) wp_die('Forbidden');
    $id=(int)($_POST['job_id']??0);
    check_admin_referer('hcdecor_publish_rollback_'.$id);
    if((string)($_POST['production_approved']??'')!=='1') wp_die('Explicit production rollback approval is required.');
    $r=hcdecor_rollback_job_publish($id);
    if(is_wp_error($r)) wp_die($r->get_error_message());
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$id.'&rolledback=1')); exit;
});

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)/publish-web',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $id=(int)$r['id'];
            if(!$id || get_post_type($id)!=='hc_content_job') return new WP_Error('job','Invalid content job.',['status'=>404]);
            if(get_current_user_id()>0 && !current_user_can('edit_post',$id)) return new WP_Error('forbidden','Forbidden.',['status'=>403]);
            if(!rest_sanitize_boolean($r->get_param('production_approved'))) return new WP_Error('approval','Explicit production publish approval is required.',['status'=>403]);
            update_post_meta($id,'hc_publish_approved_by',get_current_user_id());
            update_post_meta($id,'hc_publish_approved_at',current_time('mysql'));
            $res=hcdecor_publish_job_to_web($id);
            if(is_wp_error($res)){
                delete_post_meta($id,'hc_publish_approved_by');
                delete_post_meta($id,'hc_publish_approved_at');
            }
            return is_wp_error($res)?$res:rest_ensure_response($res);
        }
    ]);
    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)/rollback-web',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            return new WP_Error('approval','Web rollback requires explicit admin approval in WordPress.',['status'=>403]);
        }
    ]);
});

add_action('admin_footer',function(){
    if(($_GET['page']??'')!=='hcdecor-content-operations' || empty($_GET['job'])) return;
    $id=(int)$_GET['job'];
    if(get_post_type($id)!=='hc_content_job') return;
    $status=(string)get_post_meta($id,'hc_agent_status',true);
    $url=(string)get_post_meta($id,'hc_published_web_url',true);
    if($status!=='published_web' || !$url) return; ?>
    <script>
    (function(){
      const root=document.querySelector('.hcops'); if(!root)return;
      const box=document.createElement('div');
      box.style.cssText='margin:14px 0;padding:12px 14px;background:#ecf7ed;border-left:4px solid #46b450';
      box.innerHTML='<strong>Published Web</strong><br><a href="<?php echo esc_js($url);?>" target="_blank" rel="noopener">Mở Project đã publish</a>';
      const form=document.createElement('form');form.method='post';form.action='<?php echo esc_js(admin_url('admin-post.php'));?>';form.style.marginTop='10px';
      form.innerHTML='<input type="hidden" name="action" value="hcdecor_publish_rollback"><input type="hidden" name="job_id" value="<?php echo $id;?>"><input type="hidden" name="_wpnonce" value="<?php echo esc_js(wp_create_nonce('hcdecor_publish_rollback_'.$id));?>"><label style="display:block;margin:8px 0"><input type="checkbox" name="production_approved" value="1" required> Tôi xác nhận rollback sẽ thay đổi nội dung public.</label><button class="button">Xác nhận rollback bản publish gần nhất</button>';
      box.appendChild(form); root.prepend(box);
    })();
    </script><?php
});
