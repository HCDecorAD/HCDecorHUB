<?php
if (!defined('ABSPATH')) exit;

/* HCDecor Agent Intake: Project/Media -> Content Job -> AI worker. */

function hcdecor_agent_create_job($project_id,$media_ids=[],$brief=''){
    $project_id=(int)$project_id;
    if(!$project_id || get_post_type($project_id)!=='hc_project') return new WP_Error('project','Invalid project');
    $media_ids=array_values(array_unique(array_filter(array_map('intval',(array)$media_ids))));
    if(!$brief){
        $brief='Tạo nội dung dự án HCDecor từ Project và media đã chọn. Phân tích hình ảnh, chọn điểm nổi bật, viết nội dung Web, Facebook, TikTok/Reels và YouTube. Không bịa thông tin không có dữ liệu.';
    }
    $id=wp_insert_post([
        'post_type'=>'hc_content_job',
        'post_status'=>'publish',
        'post_title'=>'AI · '.get_the_title($project_id).' · '.current_time('Y-m-d H:i'),
        'post_content'=>sanitize_textarea_field($brief)
    ]);
    if(is_wp_error($id)) return $id;
    update_post_meta($id,'hc_project_id',$project_id);
    update_post_meta($id,'hc_media_ids',$media_ids);
    if($media_ids){
        $cover=(int)$media_ids[0]; $best=-1;
        foreach($media_ids as $mid){$score=(int)get_post_meta($mid,'hc_ai_cover_score',true); if($score>$best){$best=$score;$cover=(int)$mid;}}
        update_post_meta($id,'hc_cover_id',$cover);
    }
    update_post_meta($id,'hc_channels',['web','facebook','tiktok','youtube']);
    update_post_meta($id,'hc_agent_status','draft');
    update_post_meta($id,'hc_outbound',false);
    if(function_exists('hcdecor_workflow_log')) hcdecor_workflow_log($id,'draft','Created from Agent Intake');
    if(function_exists('wp_schedule_single_event')) wp_schedule_single_event(time()+5,'hcdecor_ai_process_job',[$id]);
    return $id;
}

add_action('hcdecor_ai_process_job',function($job_id){
    $job_id=(int)$job_id;
    if(get_post_type($job_id)!=='hc_content_job') return;
    $status=(string)get_post_meta($job_id,'hc_agent_status',true);
    if(!in_array($status,['draft','failed'],true)) return;
    if(!function_exists('hcdecor_ai_generate_job')) return;
    update_post_meta($job_id,'hc_agent_lock_until',time()+180);
    if(function_exists('hcdecor_workflow_set_status')) hcdecor_workflow_set_status($job_id,'processing','Scheduled AI processing');
    else update_post_meta($job_id,'hc_agent_status','processing');
    hcdecor_ai_generate_job($job_id);
},10,1);

add_filter('post_row_actions',function($actions,$post){
    if($post->post_type!=='hc_project' || !current_user_can('edit_post',$post->ID)) return $actions;
    $url=wp_nonce_url(admin_url('admin-post.php?action=hcdecor_agent_from_project&project_id='.$post->ID),'hcdecor_agent_from_project_'.$post->ID);
    $actions['hcdecor_ai']='<a href="'.esc_url($url).'">AI Content</a>';
    return $actions;
},20,2);

add_action('admin_post_hcdecor_agent_from_project',function(){
    $project=(int)($_GET['project_id']??0);
    if(!$project || !current_user_can('edit_post',$project)) wp_die('Forbidden');
    check_admin_referer('hcdecor_agent_from_project_'.$project);
    $media=(array)get_post_meta($project,'hc_project_gallery',true);
    if(!$media) $media=(array)get_post_meta($project,'hc_gallery_ids',true);
    $id=hcdecor_agent_create_job($project,$media,'');
    if(is_wp_error($id)) wp_die($id->get_error_message());
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$id.'&created=1')); exit;
});
