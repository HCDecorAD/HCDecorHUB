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

function hcdecor_publish_job_to_web($job_id){
    $job_id=(int)$job_id;
    if(get_post_type($job_id)!=='hc_content_job') return new WP_Error('job','Invalid content job');
    if((string)get_post_meta($job_id,'hc_agent_status',true)!=='approved') return new WP_Error('status','Job must be approved first.');

    $project=(int)get_post_meta($job_id,'hc_project_id',true);
    if(!$project || get_post_type($project)!=='hc_project') return new WP_Error('project','Invalid project');

    $snapshot=hcdecor_publish_snapshot($project);
    if(is_wp_error($snapshot)) return $snapshot;

    $title=(string)get_post_meta($job_id,'hc_web_title',true);
    $intro=(string)get_post_meta($job_id,'hc_web_intro',true);
    $body=(string)get_post_meta($job_id,'hc_web_body',true);
    $media=array_values(array_unique(array_filter(array_map('intval',(array)get_post_meta($job_id,'hc_media_ids',true)))));
    $cover=(int)get_post_meta($job_id,'hc_cover_id',true);
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

    update_post_meta($job_id,'hc_publish_snapshot',$snapshot);
    update_post_meta($job_id,'hc_published_project_id',$project);
    update_post_meta($job_id,'hc_published_web_at',current_time('mysql'));
    update_post_meta($job_id,'hc_published_web_url',get_permalink($project));
    update_post_meta($job_id,'hc_outbound',false);
    if(function_exists('hcdecor_workflow_set_status')) hcdecor_workflow_set_status($job_id,'published_web','Published to Web');
    else update_post_meta($job_id,'hc_agent_status','published_web');

    return ['job_id'=>$job_id,'project_id'=>$project,'url'=>get_permalink($project)];
}

function hcdecor_rollback_job_publish($job_id){
    $job_id=(int)$job_id;
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

    update_post_meta($project,'hc_project_gallery',(array)($snapshot['gallery']??[]));
    update_post_meta($project,'hc_gallery_ids',(array)($snapshot['gallery_legacy']??[]));
    update_post_meta($project,'hc_seo_meta',(string)($snapshot['seo_meta']??''));
    $cover=(int)($snapshot['cover']??0);
    if($cover && wp_attachment_is_image($cover)) set_post_thumbnail($project,$cover);
    else delete_post_thumbnail($project);

    update_post_meta($job_id,'hc_agent_status','approved');
    update_post_meta($job_id,'hc_rollback_at',current_time('mysql'));
    if(function_exists('hcdecor_workflow_log')) hcdecor_workflow_log($job_id,'approved','Rolled back Web publish');
    return ['job_id'=>$job_id,'project_id'=>$project,'url'=>get_permalink($project)];
}

add_action('admin_post_hcdecor_publish_rollback',function(){
    if(!current_user_can('publish_posts')) wp_die('Forbidden');
    $id=(int)($_POST['job_id']??0);
    check_admin_referer('hcdecor_publish_rollback_'.$id);
    $r=hcdecor_rollback_job_publish($id);
    if(is_wp_error($r)) wp_die($r->get_error_message());
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$id.'&rolledback=1')); exit;
});

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)/publish-web',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $res=hcdecor_publish_job_to_web((int)$r['id']);
            return is_wp_error($res)?$res:rest_ensure_response($res);
        }
    ]);
    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)/rollback-web',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $res=hcdecor_rollback_job_publish((int)$r['id']);
            return is_wp_error($res)?$res:rest_ensure_response($res);
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
      form.innerHTML='<input type="hidden" name="action" value="hcdecor_publish_rollback"><input type="hidden" name="job_id" value="<?php echo $id;?>"><input type="hidden" name="_wpnonce" value="<?php echo esc_js(wp_create_nonce('hcdecor_publish_rollback_'.$id));?>"><button class="button">Rollback bản publish gần nhất</button>';
      box.appendChild(form); root.prepend(box);
    })();
    </script><?php
});
