<?php
if (!defined('ABSPATH')) exit;

/* HCDecor Agent Intake: Project/Media -> Content Job -> AI worker. */

function hcdecor_agent_create_job($project_id,$media_ids=[],$brief=''){
    $project_id=is_scalar($project_id)?(int)$project_id:0;
    if(!$project_id || get_post_type($project_id)!=='hc_project') return new WP_Error('project','Invalid project');
    $media_ids=array_slice(array_values(array_filter(array_unique(array_map('intval',array_filter(is_array($media_ids)?$media_ids:[],'is_scalar'))),function($id){ return get_post_type($id)==='attachment'; })),0,60);
    if($media_ids && function_exists('hcdecor_media_ai_apply_project_recommendations')){
        $recommendation=hcdecor_media_ai_apply_project_recommendations($project_id);
        if(!is_wp_error($recommendation) && !empty($recommendation['media_ids'])){$recommended_media=$recommendation['media_ids'];$media_ids=array_slice(array_values(array_filter(array_unique(array_map('intval',array_filter(is_array($recommended_media)?$recommended_media:[],'is_scalar'))),function($id){ return get_post_type($id)==='attachment'; })),0,60);}
    }
    $brief=is_scalar($brief)?(string)$brief:'';
    if(!$brief){
        $brief='Tạo nội dung dự án HCDecor từ Project và media đã chọn. Phân tích hình ảnh, chọn điểm nổi bật, viết nội dung Web, Facebook, TikTok/Reels và YouTube. Không bịa thông tin không có dữ liệu.';
    }
    $id=wp_insert_post([
        'post_type'=>'hc_content_job',
        'post_status'=>'publish',
        'post_title'=>'AI · '.get_the_title($project_id).' · '.current_time('Y-m-d H:i'),
        'post_content'=>function_exists('hcdecor_ops_limit_text')?hcdecor_ops_limit_text($brief,20000):(function_exists('mb_substr')?mb_substr(sanitize_textarea_field($brief),0,20000):substr(sanitize_textarea_field($brief),0,20000))
    ]);
    if(is_wp_error($id)) return $id;
    update_post_meta($id,'hc_project_id',$project_id);
    update_post_meta($id,'hc_media_ids',$media_ids);
    if($media_ids){
        $recommended=(int)get_post_meta($project_id,'hc_ai_recommended_cover_id',true);
        $cover=($recommended && in_array($recommended,$media_ids,true))?$recommended:(int)$media_ids[0];
        update_post_meta($id,'hc_cover_id',$cover);
    }
    update_post_meta($id,'hc_channels',['web','facebook','tiktok','youtube']);
    if(!function_exists('hcdecor_workflow_set_status')){
        wp_delete_post($id,true);
        return new WP_Error('workflow','Workflow engine unavailable.');
    }
    if(!hcdecor_workflow_set_status($id,'draft','Created from Agent Intake')){
        wp_delete_post($id,true);
        return new WP_Error('workflow','Unable to initialize content job workflow.');
    }
    update_post_meta($id,'hc_outbound',false);
    $ai_ready=function_exists('hcdecor_ai_worker_enabled') && hcdecor_ai_worker_enabled() && function_exists('hcdecor_ai_available') && (hcdecor_ai_available('openai') || hcdecor_ai_available('gemini'));
    if($ai_ready && function_exists('wp_schedule_single_event') && !wp_next_scheduled('hcdecor_ai_process_job',[$id])) wp_schedule_single_event(time()+5,'hcdecor_ai_process_job',[$id]);
    return $id;
}

add_action('hcdecor_ai_process_job',function($job_id){
    if(!function_exists('hcdecor_ai_worker_enabled') || !hcdecor_ai_worker_enabled()) return;
    $job_id=(int)$job_id;
    if(get_post_type($job_id)!=='hc_content_job') return;
    $status_raw=get_post_meta($job_id,'hc_agent_status',true);$status=is_scalar($status_raw)?sanitize_key((string)$status_raw):'';
    if($status!=='draft') return;
    if(!function_exists('hcdecor_ai_generate_job')) return;
    if(function_exists('hcdecor_workflow_finalize_claim')){
        $now=time();
        $claim_token=wp_generate_uuid4();
        $mutex='hcdecor_claim_mutex_'.$job_id;
        if(!add_option($mutex,['token'=>$claim_token,'at'=>$now],'',false)) return;
        if(!add_post_meta($job_id,'hc_agent_claim_token',$claim_token,true)){
            hcdecor_workflow_claim_mutex_release($job_id,$claim_token);
            return;
        }
        if(!hcdecor_workflow_finalize_claim($job_id,$claim_token,$now,'Scheduled AI processing')){
            hcdecor_workflow_clear_owned_claim($job_id,$claim_token,true);
            return;
        }
        hcdecor_workflow_claim_mutex_release($job_id,$claim_token);
    }else{
        return;
    }
    hcdecor_ai_generate_job($job_id);
},10,1);

add_filter('post_row_actions',function($actions,$post){
    if($post->post_type!=='hc_project' || !current_user_can('edit_post',$post->ID)) return $actions;
    $url=wp_nonce_url(admin_url('admin-post.php?action=hcdecor_agent_from_project&project_id='.$post->ID),'hcdecor_agent_from_project_'.$post->ID);
    $actions['hcdecor_ai']='<a href="'.esc_url($url).'">AI Content</a>';
    return $actions;
},20,2);

add_action('admin_post_hcdecor_agent_from_project',function(){
    $project_raw=$_GET['project_id']??0;$project=is_scalar($project_raw)?(int)$project_raw:0;
    if(!$project || !current_user_can('edit_post',$project)) wp_die('Forbidden');
    check_admin_referer('hcdecor_agent_from_project_'.$project);
    $media_raw=get_post_meta($project,'hc_project_gallery',true);$media=is_array($media_raw)?array_slice(array_values(array_filter(array_map('intval',$media_raw))),0,60):[];
    if(!$media){$legacy_raw=get_post_meta($project,'hc_gallery_ids',true);$media=is_array($legacy_raw)?array_slice(array_values(array_filter(array_map('intval',$legacy_raw))),0,60):[];}
    $id=hcdecor_agent_create_job($project,$media,'');
    if(is_wp_error($id)) wp_die(function_exists('hcdecor_ai_safe_error')?hcdecor_ai_safe_error($id->get_error_message()):'Unable to create content job.');
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$id.'&created=1')); exit;
});
