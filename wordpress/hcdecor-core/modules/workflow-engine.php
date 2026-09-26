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
    if($old===$status) return true;
    update_post_meta($job_id,'hc_agent_status',$status);
    update_post_meta($job_id,'hc_status_changed_at',current_time('mysql'));
    if($status==='review') update_post_meta($job_id,'hc_review_entered_at',current_time('mysql'));
    elseif($old==='review') delete_post_meta($job_id,'hc_review_entered_at');
    hcdecor_workflow_log($job_id,$status,$note?:($old.' → '.$status));
    do_action('hcdecor_workflow_status_changed',$job_id,$old,$status);
    return true;
}

function hcdecor_workflow_sweep_claim_mutexes($limit=100){
    global $wpdb;
    $limit=max(1,min(100,(int)$limit));
    $cutoff=time()-120;
    $rows=$wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_id ASC LIMIT %d",$wpdb->esc_like('hcdecor_claim_mutex_').'%', $limit+1));
    $limited=count($rows)>$limit;
    $rows=array_slice((array)$rows,0,$limit);
    $stale=0; $deleted=0;
    foreach($rows as $name){
        $mutex=(array)get_option($name,[]);
        if(empty($mutex['at']) || (int)$mutex['at']<$cutoff){
            $stale++;
            if(hcdecor_workflow_claim_mutex_delete_if_same($name,$mutex)) $deleted++;
        }
    }
    update_option('hcdecor_worker_mutex_sweep_last',[
        'at'=>current_time('mysql'),'scanned'=>count($rows),'stale'=>$stale,'deleted'=>$deleted,'limited'=>$limited
    ],false);
    $next=wp_next_scheduled('hcdecor_worker_mutex_sweep_tick');
    if($limited && !$next) wp_schedule_single_event(time()+30,'hcdecor_worker_mutex_sweep_tick');
    elseif(!$limited && $next) wp_clear_scheduled_hook('hcdecor_worker_mutex_sweep_tick');
    return compact('stale','deleted','limited');
}

add_action('init',function(){ hcdecor_workflow_sweep_claim_mutexes(100); },60);
add_action('hcdecor_worker_mutex_sweep_tick',function(){ hcdecor_workflow_sweep_claim_mutexes(100); });

function hcdecor_workflow_claim_mutex_delete_if_same($key,$observed){
    global $wpdb;
    $key=(string)$key;
    if($key==='' || !$observed) return false;
    $serialized=maybe_serialize($observed);
    $deleted=$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name=%s AND option_value=%s",$key,$serialized));
    if($deleted){
        wp_cache_delete($key,'options');
        wp_cache_delete('alloptions','options');
    }
    return $deleted===1;
}

function hcdecor_workflow_clear_worker_claim($job_id,$clear_claimed=true,$clear_mutex=false){
    $job_id=(int)$job_id;
    delete_post_meta($job_id,'hc_agent_lock_until');
    delete_post_meta($job_id,'hc_agent_claim_token');
    delete_post_meta($job_id,'hc_agent_heartbeat');
    if($clear_claimed) delete_post_meta($job_id,'hc_agent_claimed_at');
    if($clear_mutex){
        $key='hcdecor_claim_mutex_'.$job_id;
        $mutex=(array)get_option($key,[]);
        if($mutex && (empty($mutex['at']) || (int)$mutex['at']<(time()-120))) hcdecor_workflow_claim_mutex_delete_if_same($key,$mutex);
    }
}

function hcdecor_workflow_claim_mutex_release($job_id,$claim_token){
    $key='hcdecor_claim_mutex_'.(int)$job_id;
    $mutex=(array)get_option($key,[]);
    if($claim_token==='' || (string)($mutex['token']??'')!==$claim_token) return false;
    return hcdecor_workflow_claim_mutex_delete_if_same($key,$mutex);
}

function hcdecor_workflow_clear_owned_claim($job_id,$claim_token,$clear_claimed=true){
    $job_id=(int)$job_id;
    if($claim_token==='' || (string)get_post_meta($job_id,'hc_agent_claim_token',true)!==$claim_token) return false;
    hcdecor_workflow_clear_worker_claim($job_id,$clear_claimed,false);
    hcdecor_workflow_claim_mutex_release($job_id,$claim_token);
    return true;
}

function hcdecor_workflow_finalize_claim($job_id,$claim_token,$now,$message='Agent claimed job'){
    $job_id=(int)$job_id;
    if($claim_token==='' || (string)get_post_meta($job_id,'hc_agent_claim_token',true)!==$claim_token) return false;
    if((string)get_post_meta($job_id,'hc_agent_status',true)!=='draft'){
        hcdecor_workflow_clear_owned_claim($job_id,$claim_token,true);
        return false;
    }
    update_post_meta($job_id,'hc_agent_claimed_at',current_time('mysql'));
    update_post_meta($job_id,'hc_agent_lock_until',(int)$now+600);
    if(hcdecor_workflow_set_status($job_id,'processing',$message)) return true;
    hcdecor_workflow_clear_owned_claim($job_id,$claim_token,true);
    return false;
}

function hcdecor_workflow_refresh_owned_claim($job_id,$claim_token,$seconds=600){
    $job_id=(int)$job_id;
    if($claim_token==='' || (string)get_post_meta($job_id,'hc_agent_claim_token',true)!==$claim_token) return false;
    if((string)get_post_meta($job_id,'hc_agent_status',true)!=='processing') return false;
    $lock=(int)get_post_meta($job_id,'hc_agent_lock_until',true);
    if($lock<=0 || $lock<time()) return false;
    $new_lock=time()+max(60,(int)$seconds);
    update_post_meta($job_id,'hc_agent_lock_until',$new_lock);
    if((string)get_post_meta($job_id,'hc_agent_claim_token',true)!==$claim_token || (string)get_post_meta($job_id,'hc_agent_status',true)!=='processing'){
        if((string)get_post_meta($job_id,'hc_agent_claim_token',true)===$claim_token && (int)get_post_meta($job_id,'hc_agent_lock_until',true)===$new_lock) delete_post_meta($job_id,'hc_agent_lock_until');
        return false;
    }
    update_post_meta($job_id,'hc_agent_heartbeat',current_time('mysql'));
    return true;
}

function hcdecor_workflow_owned_claim_active($job_id,$claim_token){
    $job_id=(int)$job_id;
    if($claim_token==='' || (string)get_post_meta($job_id,'hc_agent_claim_token',true)!==$claim_token) return false;
    if((string)get_post_meta($job_id,'hc_agent_status',true)!=='processing') return false;
    $lock=(int)get_post_meta($job_id,'hc_agent_lock_until',true);
    return $lock>0 && $lock>=time();
}

function hcdecor_workflow_finish_owned_claim($job_id,$claim_token,$status,$note=''){
    $job_id=(int)$job_id;
    if(!hcdecor_workflow_owned_claim_active($job_id,$claim_token)) return false;
    if(!hcdecor_workflow_set_status($job_id,$status,$note)) return false;
    hcdecor_workflow_clear_owned_claim($job_id,$claim_token,true);
    return true;
}

function hcdecor_workflow_claim_response($job,$claim_token){
    return rest_ensure_response([
        'id'=>$job->ID,'title'=>$job->post_title,'brief'=>$job->post_content,
        'project_id'=>(int)get_post_meta($job->ID,'hc_project_id',true),
        'channels'=>(array)get_post_meta($job->ID,'hc_channels',true),
        'media_ids'=>(array)get_post_meta($job->ID,'hc_media_ids',true),
        'cover_id'=>(int)get_post_meta($job->ID,'hc_cover_id',true),
        'status'=>'processing','claim_token'=>$claim_token,'outbound'=>false
    ]);
}

function hcdecor_workflow_recover_orphan_draft_claims($limit=20){
    $jobs=get_posts([
        'post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>max(1,min(50,(int)$limit)),'fields'=>'ids',
        'meta_query'=>[
            'relation'=>'AND',
            ['key'=>'hc_agent_status','value'=>'draft'],
            ['key'=>'hc_agent_claim_token','compare'=>'EXISTS']
        ],
        'orderby'=>'modified','order'=>'ASC'
    ]);
    $now=time(); $recovered=0;
    foreach($jobs as $job_id){
        $token=(string)get_post_meta($job_id,'hc_agent_claim_token',true);
        if($token==='') continue;
        $lock=(int)get_post_meta($job_id,'hc_agent_lock_until',true);
        if($lock>$now) continue;
        $mutex=(array)get_option('hcdecor_claim_mutex_'.$job_id,[]);
        if(!empty($mutex['at']) && (int)$mutex['at']>=($now-120)) continue;
        $claimed=strtotime((string)get_post_meta($job_id,'hc_agent_claimed_at',true))?:0;
        $modified=strtotime((string)get_post_field('post_modified',$job_id))?:0;
        $age_base=$claimed?:$modified;
        $stale_after=$claimed?120:900;
        if(!$age_base || $age_base>=($now-$stale_after)) continue;
        hcdecor_workflow_clear_worker_claim($job_id,true);
        update_post_meta($job_id,'hc_agent_recovered_at',current_time('mysql'));
        update_post_meta($job_id,'hc_agent_recovery_reason','orphan_draft_claim');
        hcdecor_workflow_log($job_id,'draft','Recovered orphan worker claim');
        $recovered++;
    }
    return $recovered;
}

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/operations/claim',[
        'methods'=>'POST',
        'permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $now=time();
            hcdecor_workflow_recover_orphan_draft_claims(50);
            $jobs=get_posts([
                'post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>50,
                'orderby'=>'date','order'=>'ASC',
                'meta_query'=>[['key'=>'hc_agent_status','value'=>'draft']]
            ]);
            foreach($jobs as $j){
                $lock=(int)get_post_meta($j->ID,'hc_agent_lock_until',true);
                if($lock>$now) continue;
                $claim_token=wp_generate_uuid4();
                $mutex='hcdecor_claim_mutex_'.$j->ID;
                if(!add_option($mutex,['token'=>$claim_token,'at'=>$now],'',false)){
                    $existing=(array)get_option($mutex,[]);
                    if(!empty($existing['at']) && (int)$existing['at']<($now-120)){
                        if(!hcdecor_workflow_claim_mutex_delete_if_same($mutex,$existing)) continue;
                        if(!add_option($mutex,['token'=>$claim_token,'at'=>$now],'',false)) continue;
                    }else continue;
                }
                if((string)get_post_meta($j->ID,'hc_agent_status',true)!=='draft'){
                    hcdecor_workflow_claim_mutex_release($j->ID,$claim_token);
                    continue;
                }
                if((int)get_post_meta($j->ID,'hc_agent_lock_until',true)>$now){
                    hcdecor_workflow_claim_mutex_release($j->ID,$claim_token);
                    continue;
                }
                if(!add_post_meta($j->ID,'hc_agent_claim_token',$claim_token,true)){
                    $existing_token=(string)get_post_meta($j->ID,'hc_agent_claim_token',true);
                    $existing_lock=(int)get_post_meta($j->ID,'hc_agent_lock_until',true);
                    if($existing_token!=='' && $existing_lock<=$now){
                        $claimed=strtotime((string)get_post_meta($j->ID,'hc_agent_claimed_at',true))?:0;
                        if($claimed && $claimed<($now-120)){
                            hcdecor_workflow_clear_worker_claim($j->ID,true,false);
                            update_post_meta($j->ID,'hc_agent_recovered_at',current_time('mysql'));
                            update_post_meta($j->ID,'hc_agent_recovery_reason','orphan_draft_claim');
                            hcdecor_workflow_log($j->ID,'draft','Recovered orphan worker claim during claim contention');
                            if(add_post_meta($j->ID,'hc_agent_claim_token',$claim_token,true)){
                                if(hcdecor_workflow_finalize_claim($j->ID,$claim_token,$now,'Agent claimed recovered job')){
                                    hcdecor_workflow_claim_mutex_release($j->ID,$claim_token);
                                    return hcdecor_workflow_claim_response($j,$claim_token);
                                }
                                hcdecor_workflow_clear_owned_claim($j->ID,$claim_token,true);
                            }
                        }
                    }
                    hcdecor_workflow_claim_mutex_release($j->ID,$claim_token);
                    continue;
                }
                if(!hcdecor_workflow_finalize_claim($j->ID,$claim_token,$now)){
                    hcdecor_workflow_claim_mutex_release($j->ID,$claim_token);
                    continue;
                }
                hcdecor_workflow_claim_mutex_release($j->ID,$claim_token);
                return hcdecor_workflow_claim_response($j,$claim_token);
            }
            return rest_ensure_response(['job'=>null,'message'=>'Queue empty']);
        }
    ]);

    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)/heartbeat',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $id=(int)$r['id'];
            if(get_post_type($id)!=='hc_content_job') return new WP_Error('not_found','Job not found',['status'=>404]);
            if((string)get_post_meta($id,'hc_agent_status',true)!=='processing') return new WP_Error('status','Job is not processing',['status'=>409]);
            $token=sanitize_text_field((string)$r->get_param('claim_token'));
            $expected=(string)get_post_meta($id,'hc_agent_claim_token',true);
            if($expected==='' || $token==='' || !hash_equals($expected,$token)) return new WP_Error('claim','Invalid worker claim token',['status'=>409]);
            if(!hcdecor_workflow_refresh_owned_claim($id,$token,600)) return new WP_Error('claim','Worker claim changed or expired during heartbeat',['status'=>409]);
            return rest_ensure_response(['ok'=>true,'id'=>$id]);
        }
    ]);

    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)/complete',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $id=(int)$r['id'];
            if(get_post_type($id)!=='hc_content_job') return new WP_Error('not_found','Job not found',['status'=>404]);
            if((string)get_post_meta($id,'hc_agent_status',true)!=='processing') return new WP_Error('status','Job is not processing',['status'=>409]);
            $token=sanitize_text_field((string)$r->get_param('claim_token'));
            $expected=(string)get_post_meta($id,'hc_agent_claim_token',true);
            if($expected==='' || $token==='' || !hash_equals($expected,$token)) return new WP_Error('claim','Invalid worker claim token',['status'=>409]);
            $lock=(int)get_post_meta($id,'hc_agent_lock_until',true);
            if($lock<=0 || $lock<time()) return new WP_Error('lock','Job lock expired; retry from Review Center',['status'=>409]);
            if(!hcdecor_workflow_owned_claim_active($id,$token)) return new WP_Error('claim','Worker claim changed before saving completion',['status'=>409]);
            $p=$r->get_json_params()?:[];
            if(function_exists('hcdecor_ops_save_fields')) hcdecor_ops_save_fields($id,$p);
            if(!hcdecor_workflow_owned_claim_active($id,$token)) return new WP_Error('claim','Worker claim changed after saving completion',['status'=>409]);
            if(!hcdecor_workflow_finish_owned_claim($id,$token,'review','Agent completed generation')) return new WP_Error('claim','Worker claim changed before completion',['status'=>409]);
            return rest_ensure_response(['ok'=>true,'id'=>$id,'status'=>'review','outbound'=>false]);
        }
    ]);

    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)/fail',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $id=(int)$r['id'];
            if(get_post_type($id)!=='hc_content_job') return new WP_Error('not_found','Job not found',['status'=>404]);
            if((string)get_post_meta($id,'hc_agent_status',true)!=='processing') return new WP_Error('status','Job is not processing',['status'=>409]);
            $token=sanitize_text_field((string)$r->get_param('claim_token'));
            $expected=(string)get_post_meta($id,'hc_agent_claim_token',true);
            if($expected==='' || $token==='' || !hash_equals($expected,$token)) return new WP_Error('claim','Invalid worker claim token',['status'=>409]);
            $lock=(int)get_post_meta($id,'hc_agent_lock_until',true);
            if($lock<=0 || $lock<time()) return new WP_Error('lock','Job lock expired; retry from Review Center',['status'=>409]);
            if(!hcdecor_workflow_owned_claim_active($id,$token)) return new WP_Error('claim','Worker claim changed before saving failure',['status'=>409]);
            $msg=sanitize_text_field((string)$r->get_param('message'));
            if(!hcdecor_workflow_finish_owned_claim($id,$token,'failed',$msg?:'Agent failed')) return new WP_Error('claim','Worker claim changed before failure update',['status'=>409]);
            update_post_meta($id,'hc_agent_error',$msg);
            return rest_ensure_response(['ok'=>true,'id'=>$id,'status'=>'failed','outbound'=>false]);
        }
    ]);
});

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','Review Center','Review Center','edit_posts','hcdecor-review','hcdecor_review_page',2);
},22);

function hcdecor_workflow_recover_stale_jobs($limit=10){
    $now=time();
    $jobs=get_posts([
        'post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>max(1,min(50,(int)$limit)),
        'meta_query'=>[['key'=>'hc_agent_status','value'=>'processing']]
    ]);
    $recovered=0;
    foreach($jobs as $job){
        $lock=(int)get_post_meta($job->ID,'hc_agent_lock_until',true);
        $claimed=strtotime((string)get_post_meta($job->ID,'hc_agent_claimed_at',true))?:0;
        $heartbeat=strtotime((string)get_post_meta($job->ID,'hc_agent_heartbeat',true))?:0;
        $token=(string)get_post_meta($job->ID,'hc_agent_claim_token',true);
        $last_worker_activity=max($claimed,$heartbeat);
        $invalid_claim=($token==='' && $last_worker_activity>0 && $last_worker_activity<($now-120));
        if($invalid_claim || ($lock>0 && $lock<$now) || ($lock<=0 && $claimed>0 && $claimed<($now-900))){
            $current_token=(string)get_post_meta($job->ID,'hc_agent_claim_token',true);
            $current_lock=(int)get_post_meta($job->ID,'hc_agent_lock_until',true);
            if($current_token!==$token || $current_lock!==$lock) continue;
            $note=$invalid_claim?'Worker claim token missing; safe retry available':'Processing lock expired; safe retry available';
            if($token!==''){
                if(!hcdecor_workflow_finish_owned_claim($job->ID,$token,'failed',$note)) continue;
            }else{
                if((string)get_post_meta($job->ID,'hc_agent_status',true)!=='processing') continue;
                if(!hcdecor_workflow_set_status($job->ID,'failed',$note)) continue;
                hcdecor_workflow_clear_worker_claim($job->ID,true);
            }
            update_post_meta($job->ID,'hc_agent_recovered_at',current_time('mysql'));
            update_post_meta($job->ID,'hc_agent_recovery_reason',$invalid_claim?'missing_claim_token':'expired_lock');
            $recovered++;
        }
    }
    return $recovered;
}

add_action('hcdecor_ai_worker_tick',function(){
    hcdecor_workflow_recover_stale_jobs(20);
},5);

add_action('admin_post_hcdecor_review_action',function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    $id=(int)($_POST['job_id']??0);
    check_admin_referer('hcdecor_review_'.$id);
    if(get_post_type($id)!=='hc_content_job') wp_die('Invalid job');
    $action=sanitize_key($_POST['review_action']??'');
    $note=sanitize_textarea_field(wp_unslash($_POST['review_note']??''));
    if($action==='approve'){
        if((string)get_post_meta($id,'hc_agent_status',true)!=='review') wp_die('Job is not ready for approval.');
        update_post_meta($id,'hc_reviewed_by',get_current_user_id());
        update_post_meta($id,'hc_reviewed_at',current_time('mysql'));
        if($note!=='') update_post_meta($id,'hc_review_note',$note);
        hcdecor_workflow_set_status($id,'approved','Approved by reviewer');
    }elseif($action==='approve_publish'){
        if(!current_user_can('publish_posts')) wp_die('Forbidden');
        if((string)get_post_meta($id,'hc_agent_status',true)!=='review') wp_die('Job is not ready for approval.');
        update_post_meta($id,'hc_reviewed_by',get_current_user_id());
        update_post_meta($id,'hc_reviewed_at',current_time('mysql'));
        if($note!=='') update_post_meta($id,'hc_review_note',$note);
        hcdecor_workflow_set_status($id,'approved','Approved by reviewer');
        if(!function_exists('hcdecor_publish_job_to_web')) wp_die('Web publisher unavailable');
        $published=hcdecor_publish_job_to_web($id);
        if(is_wp_error($published)) wp_die($published->get_error_message());
        wp_safe_redirect(admin_url('admin.php?page=hcdecor-review&job='.$id.'&published=1')); exit;
    }elseif($action==='changes'){
        delete_post_meta($id,'hc_reviewed_by');
        delete_post_meta($id,'hc_reviewed_at');
        hcdecor_workflow_set_status($id,'draft','Returned for changes');
        update_post_meta($id,'hc_review_note',$note);
    }elseif($action==='retry' && (string)get_post_meta($id,'hc_agent_status',true)==='failed'){
        delete_post_meta($id,'hc_reviewed_by');
        delete_post_meta($id,'hc_reviewed_at');
        hcdecor_workflow_set_status($id,'draft','Retry requested by reviewer');
        delete_post_meta($id,'hc_agent_lock_until');
            delete_post_meta($id,'hc_agent_claim_token');
        if(function_exists('wp_schedule_single_event')) wp_schedule_single_event(time()+3,'hcdecor_ai_process_job',[$id]);
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
      <?php
      $preflight_error='';
      if($status==='approved' && function_exists('hcdecor_publish_preflight')){
          $pf=hcdecor_publish_preflight($job_id);
          if(is_wp_error($pf)) $preflight_error=$pf->get_error_message();
      }
      if($preflight_error!==''):?><div class="notice notice-error inline"><p><strong>Publish blocked:</strong> <?php echo esc_html($preflight_error);?></p></div><?php endif;?>
      <div class="hcr-preview"><?php foreach($fields as $name=>$x):?><div class="hcr-channel"><h3><?php echo esc_html($name);?></h3><?php if($x[0]):?><strong><?php echo esc_html($x[0]);?></strong><?php endif;?><p><?php echo esc_html(trim($x[1]));?></p></div><?php endforeach;?></div>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><?php wp_nonce_field('hcdecor_review_'.$job_id);?><input type="hidden" name="action" value="hcdecor_review_action"><input type="hidden" name="job_id" value="<?php echo $job_id;?>">
        <label><strong>Ghi chú chỉnh sửa</strong></label><textarea name="review_note"></textarea>
        <div class="hcr-actions"><?php if($status==='failed'):?><button class="button button-primary" name="review_action" value="retry">Retry AI</button><?php else:?><button class="button" name="review_action" value="changes">Trả về chỉnh sửa</button><?php endif;?><?php if($status==='review'):?><button class="button" name="review_action" value="approve">Approve</button><?php if(current_user_can('publish_posts')):?><button class="button button-primary" name="review_action" value="approve_publish">Approve + Publish Web</button><?php endif;?><?php endif;?><a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-content-operations&job='.$job_id));?>">Mở Content Job</a></div>
      </form>
      <?php if($status==='approved' && $preflight_error===''):?><p><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-content-operations&job='.$job_id));?>">Publish Web trong Content Operations</a></p><?php endif;?>
    <?php else:?><p>Chưa có Content Job để review.</p><?php endif;?>
    </div></section></div></div><?php
}
