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
        'enabled'=>false,
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
    $encoded=wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    if(!is_string($encoded) || strlen($encoded)>256*1024) return new WP_Error('payload_size','Automation payload exceeds 256 KB.');
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
        'post_content'=>$encoded
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

function hcdecor_auto_safe_message($message){
    $text=sanitize_text_field((string)$message);
    return function_exists('mb_substr')?mb_substr($text,0,500):substr($text,0,500);
}
function hcdecor_auto_log($task_id,$event,$note=''){
    $log=(array)get_post_meta($task_id,'hc_auto_log',true);
    $log[]=['time'=>current_time('mysql'),'event'=>sanitize_key($event),'note'=>hcdecor_auto_safe_message($note)];
    if(count($log)>50) $log=array_slice($log,-50);
    update_post_meta($task_id,'hc_auto_log',$log);
}

function hcdecor_auto_webhook($event,$payload){
    $s=hcdecor_auto_settings();
    if(empty($s['webhook_enabled']) || empty($s['webhook_url'])) return new WP_Error('blocked','Webhook disabled.');
    $url=esc_url_raw($s['webhook_url']);
    if(!$url || !function_exists('hcdecor_conn_public_https') || !hcdecor_conn_public_https($url)) return new WP_Error('url','Webhook URL must be public HTTPS.');
    $body=['event'=>$event,'site'=>home_url('/'),'time'=>current_time('mysql'),'payload'=>$payload];
    $encoded=wp_json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    if(!is_string($encoded) || strlen($encoded)>256*1024) return new WP_Error('payload_size','Webhook payload exceeds 256 KB.');
    $r=wp_safe_remote_post($url,[
        'timeout'=>20,
        'redirection'=>0,
        'headers'=>['Content-Type'=>'application/json','X-HCDecor-Event'=>$event],
        'body'=>$encoded
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
    $settings=hcdecor_auto_settings();
    if(empty($settings['enabled'])){
        update_post_meta($task_id,'hc_auto_status','blocked');
        hcdecor_auto_log($task_id,'blocked','Automation HUB is OFF');
        return false;
    }
    $type=(string)get_post_meta($task_id,'hc_auto_type',true);
    $payload=hcdecor_auto_payload($task_id);
    update_post_meta($task_id,'hc_auto_status','running');
    update_post_meta($task_id,'hc_auto_started_at',current_time('mysql'));
    hcdecor_auto_log($task_id,'running',$type);

    if($type==='webhook'){
        if(empty($settings['webhook_enabled']) || empty($settings['webhook_url'])){
            update_post_meta($task_id,'hc_auto_status','blocked');
            hcdecor_auto_log($task_id,'blocked','Webhook outbound is OFF');
            return false;
        }
        if(empty($payload['production_approved'])){
            update_post_meta($task_id,'hc_auto_status','blocked');
            hcdecor_auto_log($task_id,'blocked','Fresh production approval is required for webhook outbound');
            return false;
        }
        // Webhook approval is one-shot: consume before the request because a network failure can be ambiguous.
        $payload['production_approved']=false;
        $payload['production_approval_consumed_at']=current_time('mysql');
        wp_update_post(['ID'=>$task_id,'post_content'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
        $r=hcdecor_auto_webhook((string)($payload['event']??'hcdecor.event'),(array)($payload['data']??[]));
        if(is_wp_error($r)){
            update_post_meta($task_id,'hc_auto_status','failed');
            update_post_meta($task_id,'hc_auto_last_error',hcdecor_auto_safe_message($r->get_error_message()));
            hcdecor_auto_log($task_id,'failed','Webhook outbound attempted; fresh production approval is required before retry.');
            return false;
        }
    }
    elseif($type==='social_publish'){
        if(empty($payload['production_approved'])){
            update_post_meta($task_id,'hc_auto_status','blocked');
            hcdecor_auto_log($task_id,'blocked','Production approval is required for social outbound');
            return false;
        }
        if(empty($settings['social_enabled'])){
            update_post_meta($task_id,'hc_auto_status','blocked');
            hcdecor_auto_log($task_id,'blocked','Social outbound is OFF');
            return false;
        }
        // Production approval is one-shot. Consume it before the outbound attempt so network ambiguity cannot auto-publish twice.
        $payload['production_approved']=false;
        $payload['production_approval_consumed_at']=current_time('mysql');
        wp_update_post(['ID'=>$task_id,'post_content'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
        $r=hcdecor_auto_webhook('hcdecor.social_publish',$payload);
        if(is_wp_error($r)){
            update_post_meta($task_id,'hc_auto_status','failed');
            update_post_meta($task_id,'hc_auto_last_error',hcdecor_auto_safe_message($r->get_error_message()));
            hcdecor_auto_log($task_id,'failed','Social outbound attempted; fresh production approval is required before retry.');
            return false;
        }
    }
    elseif($type==='evergreen'){
        if(empty($settings['evergreen_enabled']) || empty($settings['social_enabled'])){
            update_post_meta($task_id,'hc_auto_status','blocked');
            hcdecor_auto_log($task_id,'blocked','Evergreen automation is OFF');
            return false;
        }
        // Evergreen may prepare content, but it must never turn into an automatic production publish.
        update_post_meta($task_id,'hc_auto_status','blocked');
        hcdecor_auto_log($task_id,'blocked','Evergreen social publishing requires fresh explicit production approval.');
        return false;
    }
    else{
        update_post_meta($task_id,'hc_auto_status','failed');
        hcdecor_auto_log($task_id,'failed','Unknown automation type');
        return false;
    }

    update_post_meta($task_id,'hc_auto_status','done');
    update_post_meta($task_id,'hc_auto_done_at',current_time('mysql'));
    wp_update_post(['ID'=>$task_id,'post_content'=>wp_json_encode(['completed'=>true,'type'=>$type,'completed_at'=>current_time('mysql')])]);
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
    $s=hcdecor_auto_settings();
    if(!empty($s['enabled'])){
        if(!wp_next_scheduled('hcdecor_automation_tick')) wp_schedule_event(time()+20,'hcdecor_1min','hcdecor_automation_tick');
    }else{
        wp_clear_scheduled_hook('hcdecor_automation_tick');
    }
    if(!empty($s['enabled']) && !empty($s['evergreen_enabled'])){
        if(!wp_next_scheduled('hcdecor_evergreen_tick')) wp_schedule_event(time()+300,'hcdecor_daily','hcdecor_evergreen_tick');
    }else{
        wp_clear_scheduled_hook('hcdecor_evergreen_tick');
    }
},50);

function hcdecor_auto_recover_stale_running($limit=20){
    $ids=get_posts([
        'post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>max(1,min(50,(int)$limit)),'fields'=>'ids',
        'meta_query'=>[['key'=>'hc_auto_status','value'=>'running']],
        'orderby'=>'modified','order'=>'ASC'
    ]);
    $recovered=0; $now=time();
    foreach($ids as $id){
        $started=strtotime((string)get_post_meta($id,'hc_auto_started_at',true))?:0;
        $modified=strtotime((string)get_post_field('post_modified',$id))?:0;
        $reason='';
        if($started && $started<($now-900)) $reason='running_timeout';
        elseif(!$started && $modified && $modified<($now-1800)) $reason='missing_started_at';
        if($reason!==''){
            update_post_meta($id,'hc_auto_status','failed');
            update_post_meta($id,'hc_auto_last_error',$reason==='running_timeout'?'Automation task exceeded 15 minute running timeout':'Automation running state missing start timestamp for over 30 minutes');
            update_post_meta($id,'hc_auto_recovered_at',current_time('mysql'));
            update_post_meta($id,'hc_auto_recovery_reason',$reason);
            hcdecor_auto_log($id,'recovered',$reason==='running_timeout'?'Stale running task marked failed; manual retry available':'Legacy/corrupt running task missing start timestamp marked failed');
            $recovered++;
        }
    }
    return $recovered;
}

add_action('hcdecor_automation_tick',function(){
    $s=hcdecor_auto_settings();
    if(empty($s['enabled'])) return;
    hcdecor_auto_recover_stale_running(50);
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

function hcdecor_auto_cleanup_mutex_delete_if_same($observed){
    global $wpdb;
    $key='hcdecor_automation_cleanup_mutex';
    $serialized=maybe_serialize($observed);
    $deleted=$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name=%s AND option_value=%s",$key,$serialized));
    if($deleted){ wp_cache_delete($key,'options'); wp_cache_delete('alloptions','options'); }
    return $deleted===1;
}

function hcdecor_auto_cleanup_mutex_state($raw=null){
    if($raw===null) $raw=get_option('hcdecor_automation_cleanup_mutex',[]);
    if(is_array($raw)) return ['token'=>(string)($raw['token']??''),'at'=>(int)($raw['at']??0),'raw'=>$raw];
    $at=(int)$raw;
    return ['token'=>'','at'=>$at,'raw'=>$raw];
}

function hcdecor_auto_cleanup_mutex_acquire(){
    $key='hcdecor_automation_cleanup_mutex'; $now=time();
    $state=hcdecor_auto_cleanup_mutex_state();
    $invalid_array=is_array($state['raw']) && $state['raw'] && (empty($state['token']) || !$state['at']);
    if($invalid_array || ($state['at'] && $state['at']<($now-120))){
        if(!hcdecor_auto_cleanup_mutex_delete_if_same($state['raw'])) return false;
        $state=['token'=>'','at'=>0,'raw'=>[]];
    }
    if($state['at']) return false;
    $owned=['token'=>wp_generate_uuid4(),'at'=>$now];
    return add_option($key,$owned,'','no')?$owned:false;
}

function hcdecor_auto_cleanup_mutex_release($owned){
    return is_array($owned) && !empty($owned['token'])?hcdecor_auto_cleanup_mutex_delete_if_same($owned):false;
}

add_action('hcdecor_automation_cleanup_tick',function(){
    $s=hcdecor_auto_settings();
    $last_change=(array)get_option('hcdecor_automation_settings_last_change',[]);
    if(empty($last_change['cleanup_limited'])) return;
    $cleanup_mutex=hcdecor_auto_cleanup_mutex_acquire();
    if(!$cleanup_mutex){
        if(!wp_next_scheduled('hcdecor_automation_cleanup_tick')) wp_schedule_single_event(time()+60,'hcdecor_automation_cleanup_tick');
        return;
    }
    $watchdog=time()+180;
    wp_schedule_single_event($watchdog,'hcdecor_automation_cleanup_watchdog');
    $cleanup=hcdecor_auto_block_pending_for_settings($s,100);
    $last_change['blocked_tasks']=(int)($last_change['blocked_tasks']??0)+(int)($cleanup['blocked']??0);
    $last_change['scanned_tasks']=(int)($last_change['scanned_tasks']??0)+(int)($cleanup['scanned']??0);
    $last_change['cleanup_limited']=!empty($cleanup['limited']);
    $last_change['cleanup_continued_at']=current_time('mysql');
    update_option('hcdecor_automation_settings_last_change',$last_change,false);
    $released=hcdecor_auto_cleanup_mutex_release($cleanup_mutex);
    if($released){
        wp_unschedule_event($watchdog,'hcdecor_automation_cleanup_watchdog');
        if(empty($cleanup['limited'])) wp_clear_scheduled_hook('hcdecor_automation_cleanup_watchdog');
    }
    if(!empty($cleanup['limited']) && !wp_next_scheduled('hcdecor_automation_cleanup_tick')) wp_schedule_single_event(time()+60,'hcdecor_automation_cleanup_tick');
});

add_action('hcdecor_automation_cleanup_watchdog',function(){
    $last_change=(array)get_option('hcdecor_automation_settings_last_change',[]);
    if(empty($last_change['cleanup_limited'])) return;
    $state=hcdecor_auto_cleanup_mutex_state();
    if($state['at'] && $state['at']<(time()-120)) hcdecor_auto_cleanup_mutex_delete_if_same($state['raw']);
    if(!wp_next_scheduled('hcdecor_automation_cleanup_tick')) wp_schedule_single_event(time()+5,'hcdecor_automation_cleanup_tick');
});

add_action('hcdecor_evergreen_tick',function(){
    $s=hcdecor_auto_settings();
    if(empty($s['enabled']) || empty($s['evergreen_enabled']) || empty($s['social_enabled'])) return;
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
    // Social and webhook delivery are separate production actions.
    // Do not enqueue outbound tasks merely because Web publishing completed; each requires fresh explicit approval.
},10,2);

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','Automation HUB','Automation HUB','manage_options','hcdecor-automation','hcdecor_automation_page',5);
},28);

function hcdecor_auto_block_pending_for_settings($settings,$limit=100){
    $limit=max(1,min(500,(int)$limit)); $types=[]; $master_off=empty($settings['enabled']);
    if(!$master_off){
        if(empty($settings['social_enabled'])) $types[]='social_publish';
        if(empty($settings['webhook_enabled']) || empty($settings['webhook_url'])) $types[]='webhook';
        if(empty($settings['evergreen_enabled']) || empty($settings['social_enabled'])) $types[]='evergreen';
    }
    if(!$master_off && !$types) return ['blocked'=>0,'scanned'=>0,'limited'=>false];
    $meta=[
        'relation'=>'AND',
        ['key'=>'hc_auto_status','value'=>['queued','scheduled'],'compare'=>'IN']
    ];
    if(!$master_off) $meta[]=['key'=>'hc_auto_type','value'=>array_values(array_unique($types)),'compare'=>'IN'];
    $ids=get_posts([
        'post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>$limit+1,'fields'=>'ids',
        'meta_query'=>$meta,'orderby'=>'ID','order'=>'ASC'
    ]);
    $limited=count($ids)>$limit;
    if($limited) $ids=array_slice($ids,0,$limit);
    foreach($ids as $id){
        $type=(string)get_post_meta($id,'hc_auto_type',true);
        $reason=$master_off?'Automation HUB is OFF':($type==='social_publish'?'Social outbound is OFF':($type==='webhook'?'Webhook outbound is OFF':'Evergreen social outbound is OFF'));
        update_post_meta($id,'hc_auto_status','blocked');
        hcdecor_auto_log($id,'blocked',$reason.' after settings change');
    }
    return ['blocked'=>count($ids),'scanned'=>count($ids),'limited'=>$limited];
}

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
    // Persist only a public HTTPS webhook. Runtime validates again before every outbound request.
    if($new['webhook_enabled'] && (!$new['webhook_url'] || !function_exists('hcdecor_conn_public_https') || !hcdecor_conn_public_https($new['webhook_url']))){
        $new['webhook_enabled']=false;
        $new['webhook_url']='';
    }
    // Do not allow social outbound without a configured webhook connector.
    if($new['social_enabled'] && (!$new['webhook_enabled'] || !$new['webhook_url'])) $new['social_enabled']=false;
    // Evergreen only produces social outbound; never leave it enabled without social.
    if($new['evergreen_enabled'] && !$new['social_enabled']) $new['evergreen_enabled']=false;
    update_option('hcdecor_automation_settings',$new,false);
    $cleanup=hcdecor_auto_block_pending_for_settings($new,500);
    update_option('hcdecor_automation_settings_last_change',[
        'at'=>current_time('mysql'),
        'by'=>get_current_user_id(),
        'enabled'=>(bool)$new['enabled'],
        'social_enabled'=>(bool)$new['social_enabled'],
        'webhook_enabled'=>(bool)$new['webhook_enabled'],
        'evergreen_enabled'=>(bool)$new['evergreen_enabled'],
        'blocked_tasks'=>(int)($cleanup['blocked']??0),
        'scanned_tasks'=>(int)($cleanup['scanned']??0),
        'cleanup_limited'=>!empty($cleanup['limited'])
    ],false);
    wp_clear_scheduled_hook('hcdecor_automation_cleanup_tick');
    wp_clear_scheduled_hook('hcdecor_automation_cleanup_watchdog');
    if(!empty($cleanup['limited'])) wp_schedule_single_event(time()+60,'hcdecor_automation_cleanup_tick');
    if(!$new['enabled']){
        wp_clear_scheduled_hook('hcdecor_automation_tick');
        wp_clear_scheduled_hook('hcdecor_evergreen_tick');
    }else{
        if(!wp_next_scheduled('hcdecor_automation_tick')) wp_schedule_event(time()+20,'hcdecor_1min','hcdecor_automation_tick');
        if($new['evergreen_enabled']){
            if(!wp_next_scheduled('hcdecor_evergreen_tick')) wp_schedule_event(time()+300,'hcdecor_daily','hcdecor_evergreen_tick');
        }else wp_clear_scheduled_hook('hcdecor_evergreen_tick');
    }
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-automation&saved=1')); exit;
});

add_action('admin_post_hcdecor_automation_retry',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    $id=(int)($_POST['task_id']??0); check_admin_referer('hcdecor_automation_retry_'.$id);
    if(get_post_type($id)!=='hc_automation_task') wp_die('Invalid task');
    $current_status=(string)get_post_meta($id,'hc_auto_status',true);
    if(!in_array($current_status,['failed','blocked'],true)) wp_die('Only failed or blocked automation tasks can be retried.');
    $s=hcdecor_auto_settings();
    if(empty($s['enabled'])) wp_die('Automation HUB is disabled.');
    $type=(string)get_post_meta($id,'hc_auto_type',true);
    if($type==='social_publish' && empty($s['social_enabled'])) wp_die('Social outbound is disabled.');
    if(in_array($type,['social_publish','webhook'],true)){
        if((string)($_POST['production_approved']??'')!=='1') wp_die('Explicit production approval is required to retry outbound delivery.');
        $payload=hcdecor_auto_payload($id);
        $payload['production_approved']=true;
        $payload['production_approved_by']=get_current_user_id();
        $payload['production_approved_at']=current_time('mysql');
        $encoded=wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        if(!is_string($encoded) || strlen($encoded)>256*1024) wp_die('Automation payload exceeds 256 KB.');
        wp_update_post(['ID'=>$id,'post_content'=>$encoded]);
    }
    if($type==='webhook' && (empty($s['webhook_enabled']) || empty($s['webhook_url']))) wp_die('Webhook outbound is disabled.');
    if($type==='evergreen' && (empty($s['evergreen_enabled']) || empty($s['social_enabled']))) wp_die('Evergreen social outbound is disabled.');
    update_post_meta($id,'hc_auto_status','queued');
    update_post_meta($id,'hc_auto_run_at',time());
    update_post_meta($id,'hc_auto_attempts',0);
    delete_post_meta($id,'hc_auto_last_error');
    delete_post_meta($id,'hc_auto_started_at');
    delete_post_meta($id,'hc_auto_done_at');
    delete_post_meta($id,'hc_auto_recovered_at');
    delete_post_meta($id,'hc_auto_recovery_reason');
    update_post_meta($id,'hc_auto_manual_retry_at',current_time('mysql'));
    update_post_meta($id,'hc_auto_manual_retry_by',get_current_user_id());
    hcdecor_auto_log($id,'manual_retry','Queued by administrator');
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-automation&retried=1')); exit;
});

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/automation/status',[
        'methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(){
            $counts=[]; foreach(hcdecor_auto_statuses() as $s){$q=new WP_Query(['post_type'=>'hc_automation_task','post_status'=>'publish','posts_per_page'=>1,'meta_key'=>'hc_auto_status','meta_value'=>$s]);$counts[$s]=(int)$q->found_posts;}
            $s=hcdecor_auto_settings();
            return rest_ensure_response([
                'settings'=>[
                    'enabled'=>!empty($s['enabled']),
                    'webhook_enabled'=>!empty($s['webhook_enabled']),
                    'webhook_configured'=>!empty($s['webhook_url']),
                    'social_enabled'=>!empty($s['social_enabled']),
                    'evergreen_enabled'=>!empty($s['evergreen_enabled']),
                    'evergreen_days'=>(int)$s['evergreen_days'],
                    'max_attempts'=>(int)$s['max_attempts'],
                    'retry_minutes'=>(int)$s['retry_minutes']
                ],
                'queue'=>$counts,
                'outbound'=>!empty($s['social_enabled'])
            ]);
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
    $s=hcdecor_auto_settings(); $social=function_exists('hcdecor_social_settings')?hcdecor_social_settings():[];
    $tasks=get_posts(['post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>40,'orderby'=>'date','order'=>'DESC']);
    $counts=array_fill_keys(hcdecor_auto_statuses(),0);
    foreach($counts as $status=>$zero){$q=new WP_Query(['post_type'=>'hc_automation_task','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','meta_key'=>'hc_auto_status','meta_value'=>$status]);$counts[$status]=(int)$q->found_posts;}
    $week=time()-3*DAY_IN_SECONDS; $days=[];for($i=0;$i<7;$i++){$ts=$week+$i*DAY_IN_SECONDS;$days[wp_date('Y-m-d',$ts)]=['ts'=>$ts,'items'=>[]];}
    foreach($tasks as $t){$run=(int)get_post_meta($t->ID,'hc_auto_run_at',true);$key=$run?wp_date('Y-m-d',$run):'';if(isset($days[$key]))$days[$key]['items'][]=$t;}
    $ready=[];foreach(['facebook'=>'Facebook','tiktok'=>'TikTok','youtube'=>'YouTube'] as $key=>$label){$ready[$label]=function_exists('hcdecor_social_connection_state')?hcdecor_social_connection_state($key):['configured'=>false,'tested'=>false,'state'=>'disconnected'];}
    ?>
    <div class="wrap hca"><style>
    .hca{max-width:1500px}.hca *{box-sizing:border-box}.hca-head{display:flex;justify-content:space-between;gap:16px;align-items:center;margin:16px 0}.hca-head h1{font-size:28px;margin:0}.hca-sub{color:#646970;margin:5px 0}.hca-kpi{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}.hca-card,.hca-panel{background:#fff;border:1px solid #dcdcde;border-radius:14px}.hca-card{padding:14px}.hca-num{font-size:25px;font-weight:800}.hca-flow{display:grid;grid-template-columns:repeat(7,1fr);gap:7px;margin:12px 0}.hca-step{background:#fff;border:1px solid #e3e5e8;border-radius:13px;padding:12px;text-align:center;font-weight:700}.hca-step small{display:block;color:#646970;font-weight:400;margin-top:4px}.hca-main{display:grid;grid-template-columns:1.15fr .85fr;gap:12px}.hca-panel{overflow:hidden}.hca-title{padding:13px 15px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center}.hca-title h2{font-size:15px;margin:0}.hca-table{width:100%;border-collapse:collapse}.hca-table th,.hca-table td{padding:10px;border-bottom:1px solid #eee;text-align:left}.hca-pill{display:inline-block;padding:4px 8px;border-radius:99px;background:#eef2ff;font-size:11px;font-weight:700}.hca-pill.done{background:#dcfce7;color:#166534}.hca-pill.failed,.hca-pill.blocked{background:#fee2e2;color:#991b1b}.hca-cal{display:grid;grid-template-columns:repeat(7,1fr);min-height:310px}.hca-day{border-right:1px solid #eee;padding:9px}.hca-day:last-child{border:0}.hca-day strong{display:block;text-align:center}.hca-event{margin-top:9px;padding:7px;border-radius:8px;background:#eef6ff;font-size:11px}.hca-bottom{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px}.hca-connect{display:grid;grid-template-columns:repeat(3,1fr);gap:9px;padding:14px}.hca-social{border:1px solid #eee;border-radius:11px;padding:12px}.hca-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#d63638;margin-right:5px}.hca-dot.ok{background:#00a32a}.hca-settings{padding:14px}.hca-settings label{display:block;margin:8px 0}.hca-settings input[type=url]{width:100%}@media(max-width:1050px){.hca-main,.hca-bottom{grid-template-columns:1fr}.hca-flow{grid-template-columns:repeat(4,1fr)}}@media(max-width:700px){.hca-kpi{grid-template-columns:1fr 1fr}.hca-flow{display:flex;overflow:auto}.hca-step{min-width:150px}.hca-cal{overflow:auto;grid-template-columns:repeat(7,150px)}}
    </style>
    <div class="hca-head"><div><h1>⚙ Automation HUB</h1><p class="hca-sub">Nội dung → AI → Review → Web → Social → Evergreen → Báo cáo</p></div><div><a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-social-connectors'));?>">Social Accounts</a> <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-agent'));?>">+ Tạo nội dung</a></div></div>
    <div class="hca-kpi"><div class="hca-card"><div class="hca-num"><?php echo (int)$counts['queued']+(int)$counts['scheduled'];?></div><small>Đang chờ</small></div><div class="hca-card"><div class="hca-num"><?php echo (int)$counts['running'];?></div><small>Đang xử lý</small></div><div class="hca-card"><div class="hca-num"><?php echo (int)$counts['done'];?></div><small>Đã hoàn thành</small></div><div class="hca-card"><div class="hca-num"><?php echo (int)$counts['failed']+(int)$counts['blocked'];?></div><small>Lỗi / Cần xử lý</small></div></div>
    <div class="hca-flow"><?php foreach([['Tạo nội dung','Project / Media'],['AI xử lý','Văn bản + hình ảnh'],['Review','Phê duyệt'],['Đăng Web','WordPress'],['Phân phối đa kênh','Facebook · TikTok · YouTube'],['Evergreen','Tái sử dụng'],['Báo cáo','Thống kê']] as $x):?><div class="hca-step"><?php echo esc_html($x[0]);?><small><?php echo esc_html($x[1]);?></small></div><?php endforeach;?></div>
    <div class="hca-main"><section class="hca-panel"><div class="hca-title"><h2>↕ Hàng đợi tự động</h2><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-agent'));?>">+ Tạo job</a></div><table class="hca-table"><thead><tr><th>#</th><th>Nội dung</th><th>Loại</th><th>Lịch chạy</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody><?php foreach(array_slice($tasks,0,8) as $t):$st=(string)get_post_meta($t->ID,'hc_auto_status',true);$run=(int)get_post_meta($t->ID,'hc_auto_run_at',true);$type=(string)get_post_meta($t->ID,'hc_auto_type',true);?><tr><td>#<?php echo $t->ID;?></td><td><?php echo esc_html($t->post_title);?></td><td><?php echo esc_html($type);?></td><td><?php echo $run?esc_html(wp_date('d/m H:i',$run)):'—';?></td><td><span class="hca-pill <?php echo esc_attr($st);?>"><?php echo esc_html(strtoupper($st));?></span></td><td><?php if(in_array($st,['blocked','failed'],true)):?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="hcdecor_automation_retry"><input type="hidden" name="task_id" value="<?php echo (int)$t->ID;?>"><?php wp_nonce_field('hcdecor_automation_retry_'.$t->ID);?><?php if(in_array($type,['social_publish','webhook'],true)):?><label><input type="checkbox" name="production_approved" value="1" required> Phê duyệt production</label><?php endif;?><button class="button">Retry</button></form><?php else:?>—<?php endif;?></td></tr><?php endforeach;if(!$tasks):?><tr><td colspan="6">Queue trống.</td></tr><?php endif;?></tbody></table></section>
    <section class="hca-panel"><div class="hca-title"><h2>📅 Lịch đăng</h2><span>7 ngày</span></div><div class="hca-cal"><?php foreach($days as $day):?><div class="hca-day"><strong><?php echo esc_html(wp_date('D d/m',$day['ts']));?></strong><?php foreach(array_slice($day['items'],0,4) as $t):?><div class="hca-event"><?php $r=(int)get_post_meta($t->ID,'hc_auto_run_at',true);echo esc_html(wp_date('H:i',$r).' · '.get_post_meta($t->ID,'hc_auto_type',true));?></div><?php endforeach;?></div><?php endforeach;?></div></section></div>
    <div class="hca-bottom"><section class="hca-panel"><div class="hca-title"><h2>Social Accounts</h2><a href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-social-connectors'));?>">Quản lý kết nối</a></div><div class="hca-connect"><?php foreach($ready as $name=>$state):$ok=!empty($state['configured']);?><div class="hca-social"><strong><?php echo esc_html($name);?></strong><p><span class="hca-dot <?php echo $ok?'ok':'';?>"></span><?php echo esc_html($ok?(!empty($state['tested'])?'Sẵn sàng':'Đã cấu hình · cần Test'):'Chưa kết nối');?></p></div><?php endforeach;?></div></section>
    <section class="hca-panel"><div class="hca-title"><h2>Automation Settings</h2></div><form class="hca-settings" method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="hcdecor_automation_settings"><?php wp_nonce_field('hcdecor_automation_settings');?><label><input type="checkbox" name="enabled" <?php checked($s['enabled']);?>> Master automation</label><label><input type="checkbox" name="webhook_enabled" <?php checked($s['webhook_enabled']);?>> Webhook connector</label><input type="url" name="webhook_url" value="<?php echo esc_attr($s['webhook_url']);?>" placeholder="https://.../webhook"><label><input type="checkbox" name="social_enabled" <?php checked($s['social_enabled']);?>> Social publishing</label><label><input type="checkbox" name="evergreen_enabled" <?php checked($s['evergreen_enabled']);?>> Evergreen</label><input type="hidden" name="evergreen_days" value="<?php echo (int)$s['evergreen_days'];?>"><input type="hidden" name="max_attempts" value="<?php echo (int)$s['max_attempts'];?>"><input type="hidden" name="retry_minutes" value="<?php echo (int)$s['retry_minutes'];?>"><p><button class="button button-primary">Lưu Automation</button></p></form></section></div>
    </div><?php
}
