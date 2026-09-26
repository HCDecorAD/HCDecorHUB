<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor System Health
 * Production readiness, cron repair, queue visibility, daily Drive report.
 */

function hcdecor_health_required_modules(){
    return [
        'background-sync.php','content-operations.php','workflow-engine.php','ai-providers.php',
        'media-intelligence.php','agent-intake.php','ai-workspace.php','web-publisher.php',
        'automation-hub.php','automation-recipes.php','drive-vault.php','drive-inbox.php','project-vault.php','data-backup.php','data-restore.php',
        'project-publishing.php','media-manager.php','hub-dashboard.php','admin-cleanup.php'
    ];
}

function hcdecor_health_queue_counts(){
    $out=['draft'=>0,'processing'=>0,'review'=>0,'approved'=>0,'published_web'=>0,'failed'=>0];
    foreach(array_keys($out) as $status){
        $q=new WP_Query([
            'post_type'=>'hc_content_job','post_status'=>'publish','posts_per_page'=>1,
            'meta_key'=>'hc_agent_status','meta_value'=>$status,'fields'=>'ids'
        ]);
        $out[$status]=(int)$q->found_posts;
    }
    return $out;
}

function hcdecor_health_automation_counts(){
    $out=['queued'=>0,'scheduled'=>0,'running'=>0,'done'=>0,'failed'=>0,'blocked'=>0];
    if(!post_type_exists('hc_automation_task')) return $out;
    foreach(array_keys($out) as $status){
        $q=new WP_Query([
            'post_type'=>'hc_automation_task','post_status'=>'publish','posts_per_page'=>1,
            'meta_key'=>'hc_auto_status','meta_value'=>$status,'fields'=>'ids'
        ]);
        $out[$status]=(int)$q->found_posts;
    }
    return $out;
}

function hcdecor_health_crons(){
    return [
        'background_sync'=>[
            'hook'=>'hcdecor_background_sync',
            'scheduled'=>(bool)wp_next_scheduled('hcdecor_background_sync'),
            'next'=>(int)(wp_next_scheduled('hcdecor_background_sync')?:0)
        ],
        'ai_worker'=>[
            'hook'=>'hcdecor_ai_worker_tick',
            'scheduled'=>(bool)wp_next_scheduled('hcdecor_ai_worker_tick'),
            'next'=>(int)(wp_next_scheduled('hcdecor_ai_worker_tick')?:0)
        ],
        'automation'=>[
            'hook'=>'hcdecor_automation_tick',
            'scheduled'=>(bool)wp_next_scheduled('hcdecor_automation_tick'),
            'next'=>(int)(wp_next_scheduled('hcdecor_automation_tick')?:0)
        ],
        'evergreen'=>[
            'hook'=>'hcdecor_evergreen_tick',
            'scheduled'=>(bool)wp_next_scheduled('hcdecor_evergreen_tick'),
            'next'=>(int)(wp_next_scheduled('hcdecor_evergreen_tick')?:0)
        ],
        'drive_inbox'=>[
            'hook'=>'hcdecor_drive_inbox_tick',
            'scheduled'=>(bool)wp_next_scheduled('hcdecor_drive_inbox_tick'),
            'next'=>(int)(wp_next_scheduled('hcdecor_drive_inbox_tick')?:0)
        ],
        'daily_backup'=>[
            'hook'=>'hcdecor_backup_daily',
            'scheduled'=>(bool)wp_next_scheduled('hcdecor_backup_daily'),
            'next'=>(int)(wp_next_scheduled('hcdecor_backup_daily')?:0)
        ],
        'daily_health'=>[
            'hook'=>'hcdecor_health_daily_report',
            'scheduled'=>(bool)wp_next_scheduled('hcdecor_health_daily_report'),
            'next'=>(int)(wp_next_scheduled('hcdecor_health_daily_report')?:0)
        ]
    ];
}

function hcdecor_health_modules(){
    $base=plugin_dir_path(__FILE__);
    $out=[];
    foreach(hcdecor_health_required_modules() as $m){
        $out[$m]=is_readable($base.$m);
    }
    return $out;
}

function hcdecor_health_provider($provider){
    if(!function_exists('hcdecor_ai_available') || !hcdecor_ai_available($provider)) return 'off';
    $test=(string)get_option('hcdecor_ai_'.$provider.'_test_status','');
    return $test==='ok'?'ok':($test==='error'?'error':'configured');
}

function hcdecor_health_actionable_blocked_count(){
    $tasks=get_posts([
        'post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>100,'fields'=>'ids',
        'meta_query'=>[['key'=>'hc_auto_status','value'=>'blocked']]
    ]);
    $settings=function_exists('hcdecor_auto_settings')?hcdecor_auto_settings():[];
    $count=0;
    foreach($tasks as $id){
        $type=(string)get_post_meta($id,'hc_auto_type',true);
        if(empty($settings['enabled'])) continue;
        if($type==='social_publish' && empty($settings['social_enabled'])) continue;
        if($type==='webhook' && (empty($settings['webhook_enabled']) || empty($settings['webhook_url']))) continue;
        if($type==='evergreen' && empty($settings['evergreen_enabled'])) continue;
        $count++;
    }
    return $count;
}

function hcdecor_health_snapshot(){
    $modules=hcdecor_health_modules();
    $crons=hcdecor_health_crons();
    $queue=hcdecor_health_queue_counts();
    $auto=hcdecor_health_automation_counts();
    $drive_configured=function_exists('hcdecor_drive_configured')&&hcdecor_drive_configured();
    $drive_test=(string)get_option('hcdecor_drive_test_status','');
    $auto_settings=function_exists('hcdecor_auto_settings')?hcdecor_auto_settings():[];
    $inbox_settings=function_exists('hcdecor_drive_inbox_settings')?hcdecor_drive_inbox_settings():[];
    $cron_required=['background_sync'=>true,'ai_worker'=>true,'backup'=>true,'health_report'=>true];
    $cron_required['automation']=!isset($auto_settings['enabled']) || !empty($auto_settings['enabled']);
    $cron_required['evergreen']=!empty($auto_settings['evergreen_enabled']);
    $cron_required['drive_inbox']=!isset($inbox_settings['enabled']) || !empty($inbox_settings['enabled']);
    $bridge=(string)get_option('hcdecor_bridge_token','');
    $actionable_blocked=hcdecor_health_actionable_blocked_count();
    $auto_recovered_24h=0;
    $auto_recovery_reasons=['running_timeout'=>0,'missing_started_at'=>0];
    $auto_recovered_ids=get_posts(['post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>100,'fields'=>'ids','meta_query'=>[['key'=>'hc_auto_recovered_at','value'=>wp_date('Y-m-d H:i:s',time()-DAY_IN_SECONDS),'compare'=>'>=','type'=>'DATETIME']]]);
    foreach($auto_recovered_ids as $aid){
        $at=strtotime((string)get_post_meta($aid,'hc_auto_recovered_at',true))?:0;
        if($at && $at>=time()-DAY_IN_SECONDS){
            $auto_recovered_24h++;
            $reason=(string)get_post_meta($aid,'hc_auto_recovery_reason',true);
            if(isset($auto_recovery_reasons[$reason])) $auto_recovery_reasons[$reason]++;
        }
    }
    $backup_last=(string)get_option('hcdecor_backup_last_at','');
    $backup_ts=$backup_last!==''?strtotime($backup_last):0;
    $backup_age=$backup_ts?max(0,current_time('timestamp')-$backup_ts):null;
    $inbox_last=(string)get_option('hcdecor_drive_inbox_last_at','');
    $inbox_ts=$inbox_last!==''?strtotime($inbox_last):0;
    $inbox_age=$inbox_ts?max(0,current_time('timestamp')-$inbox_ts):null;
    $stale_processing=0;
    $processing_without_token=0;
    $processing_lock_expiring=0;
    $recovered_24h=0;
    $recovery_reasons=['missing_claim_token'=>0,'expired_lock'=>0,'orphan_draft_claim'=>0];
    $oldest_review_age=0;
    $review_ids=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>50,'fields'=>'ids','meta_key'=>'hc_agent_status','meta_value'=>'review']);
    foreach($review_ids as $rid){
        $entered=strtotime((string)get_post_meta($rid,'hc_review_entered_at',true))?:0;
        if(!$entered) $entered=strtotime((string)get_post_field('post_modified',$rid))?:0;
        if($entered) $oldest_review_age=max($oldest_review_age,max(0,current_time('timestamp')-$entered));
    }
    $processing_ids=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>50,'fields'=>'ids','meta_key'=>'hc_agent_status','meta_value'=>'processing']);
    $draft_orphan_claims=0;
    $draft_claim_ids=get_posts([
        'post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>50,'fields'=>'ids',
        'meta_query'=>[
            'relation'=>'AND',
            ['key'=>'hc_agent_status','value'=>'draft'],
            ['key'=>'hc_agent_claim_token','compare'=>'EXISTS']
        ]
    ]);
    $now=time();
    foreach($draft_claim_ids as $did){
        $token=(string)get_post_meta($did,'hc_agent_claim_token',true);
        if($token==='') continue;
        $lock=(int)get_post_meta($did,'hc_agent_lock_until',true);
        if($lock>$now) continue;
        $claimed=strtotime((string)get_post_meta($did,'hc_agent_claimed_at',true))?:0;
        $modified=strtotime((string)get_post_field('post_modified',$did))?:0;
        $base=$claimed?:$modified;
        $stale_after=$claimed?120:900;
        if($base && $base<($now-$stale_after)) $draft_orphan_claims++;
    }
    foreach($processing_ids as $pid){
        $lock=(int)get_post_meta($pid,'hc_agent_lock_until',true);
        $claimed=strtotime((string)get_post_meta($pid,'hc_agent_claimed_at',true))?:0;
        $heartbeat=strtotime((string)get_post_meta($pid,'hc_agent_heartbeat',true))?:0;
        $token=(string)get_post_meta($pid,'hc_agent_claim_token',true);
        if($token==='' && max($claimed,$heartbeat)>0 && max($claimed,$heartbeat)<($now-120)) $processing_without_token++;
        if($lock>$now && ($lock-$now)<=120) $processing_lock_expiring++;
        if(($lock>0 && $lock<$now) || ($lock<=0 && $claimed>0 && $claimed<($now-900))) $stale_processing++;
    }
    $project_total=(int)(new WP_Query(['post_type'=>'hc_project','post_status'=>['publish','draft','private'],'posts_per_page'=>1,'fields'=>'ids']))->found_posts;
    $vault_synced=(int)(new WP_Query(['post_type'=>'hc_project','post_status'=>['publish','draft','private'],'posts_per_page'=>1,'meta_key'=>'hc_drive_project_file_id','fields'=>'ids']))->found_posts;
    $vault_errors=(int)(new WP_Query(['post_type'=>'hc_project','post_status'=>['publish','draft','private'],'posts_per_page'=>1,'meta_key'=>'hc_drive_project_error','meta_compare'=>'EXISTS','fields'=>'ids']))->found_posts;
    $vault_retrying=(int)(new WP_Query(['post_type'=>'hc_project','post_status'=>['publish','draft','private'],'posts_per_page'=>1,'meta_key'=>'hc_drive_project_retry_count','meta_value'=>0,'meta_compare'=>'>','fields'=>'ids']))->found_posts;
    $backup_retry=(int)get_option('hcdecor_backup_retry_count',0);
    $vault_bulk=(array)get_option('hcdecor_project_vault_bulk_last_result',[]);
    $vault_bulk_at=(string)get_option('hcdecor_project_vault_bulk_last_at','');
    $vault_bulk_ts=$vault_bulk_at!==''?(strtotime($vault_bulk_at)?:0):0;
    $inbox_result=(array)get_option('hcdecor_drive_inbox_last_result',[]);
    $inbox_unlinked=max(0,(int)($inbox_result['imported']??0)-(int)($inbox_result['linked']??0));
    $published_7d=(int)(new WP_Query(['post_type'=>'hc_content_job','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','meta_query'=>[['key'=>'hc_published_web_at','value'=>wp_date('Y-m-d H:i:s',current_time('timestamp')-7*DAY_IN_SECONDS),'compare'=>'>=','type'=>'DATETIME']]]))->found_posts;
    $created_7d=(int)(new WP_Query(['post_type'=>'hc_content_job','post_status'=>'publish','posts_per_page'=>1,'date_query'=>[['after'=>'7 days ago']],'fields'=>'ids']))->found_posts;
    $backup_cron=(int)(wp_next_scheduled('hcdecor_backup_daily')?:0);
    $last_auto_repair=(array)get_option('hcdecor_health_auto_repair_last',[]);
    $last_auto_repair_ts=!empty($last_auto_repair['at'])?(strtotime((string)$last_auto_repair['at'])?:0):0;
    $vault_stale=0;
    $vault_stale_scanned=0;
    for($page=1;$page<=4;$page++){
        $vault_ids=get_posts([
            'post_type'=>'hc_project','post_status'=>['publish','draft','private'],'numberposts'=>50,'fields'=>'ids',
            'meta_key'=>'hc_drive_project_file_id','meta_compare'=>'EXISTS','paged'=>$page,'orderby'=>'ID','order'=>'ASC'
        ]);
        if(!$vault_ids) break;
        foreach($vault_ids as $pid){
            $vault_stale_scanned++;
            $synced=strtotime((string)get_post_meta($pid,'hc_drive_project_synced_at',true))?:0;
            $modified=strtotime((string)get_post_field('post_modified',$pid))?:0;
            if($modified>0 && ($synced===0 || $modified>$synced+5)) $vault_stale++;
        }
        if(count($vault_ids)<50) break;
    }

    $issues=[];
    foreach($modules as $name=>$ok) if(!$ok) $issues[]='Missing module: '.$name;
    foreach($crons as $name=>$x) if(!empty($cron_required[$name]) && !$x['scheduled']) $issues[]='Cron missing: '.$name;
    if(hcdecor_health_provider('openai')==='error') $issues[]='OpenAI test error';
    if(hcdecor_health_provider('gemini')==='error') $issues[]='Gemini test error';
    if($drive_configured && $drive_test==='error') $issues[]='Google Drive connection error';
    if($auto['failed']>0) $issues[]='Automation failed: '.$auto['failed'];
    if($actionable_blocked>0) $issues[]='Automation blocked: '.$actionable_blocked;
    if(!empty(((array)get_option('hcdecor_automation_settings_last_change',[]))['cleanup_limited'])) $issues[]='Automation settings cleanup backlog exceeds bounded pass';
    if($bridge==='') $issues[]='Agent Bridge token missing';
    if($drive_configured && $backup_ts===0) $issues[]='Backup has never completed';
    elseif($drive_configured && $backup_age>129600) $issues[]='Backup is stale (>36h)';
    if($drive_configured && $project_total>0 && $vault_synced<$project_total) $issues[]='Project Vault pending: '.($project_total-$vault_synced);
    if($vault_errors>0) $issues[]='Project Vault errors: '.$vault_errors;
    if((int)($vault_bulk['failed']??0)>0 && $vault_bulk_ts && (current_time('timestamp')-$vault_bulk_ts)<=86400) $issues[]='Recent Project Vault bulk sync failed: '.(int)$vault_bulk['failed'];
    if(!empty($inbox_settings['enabled']) && (int)($inbox_result['failed']??0)>0) $issues[]='Last Drive Inbox run failed: '.(int)$inbox_result['failed'];
    $backup_running=(bool)get_transient('hcdecor_backup_running');
    if($vault_stale>0) $issues[]='Project Vault stale: '.$vault_stale.($vault_synced>$vault_stale_scanned?' (first '.$vault_stale_scanned.' scanned)':'');
    if(!empty($inbox_settings['enabled']) && $inbox_ts===0) $issues[]='Drive Inbox has never completed';
    elseif(!empty($inbox_settings['enabled']) && $inbox_age>1800) $issues[]='Drive Inbox is stale (>30 min)';
    $recovered_ids=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>100,'fields'=>'ids','meta_query'=>[['key'=>'hc_agent_recovered_at','value'=>wp_date('Y-m-d H:i:s',$now-DAY_IN_SECONDS),'compare'=>'>=','type'=>'DATETIME']]]);
    foreach($recovered_ids as $rid){
        $rt=strtotime((string)get_post_meta($rid,'hc_agent_recovered_at',true))?:0;
        if($rt && $rt>=($now-DAY_IN_SECONDS)){
            $recovered_24h++;
            $reason=(string)get_post_meta($rid,'hc_agent_recovery_reason',true);
            if(isset($recovery_reasons[$reason])) $recovery_reasons[$reason]++;
        }
    }

    if($stale_processing>0) $issues[]='Stale processing jobs: '.$stale_processing;
    if($processing_without_token>0) $issues[]='Processing jobs missing claim token >2m: '.$processing_without_token;
    if($draft_orphan_claims>0) $issues[]='Draft jobs with orphan worker claim: '.$draft_orphan_claims;
    if($oldest_review_age>86400) $issues[]='Review queue oldest item >24h';

    $score=100;
    $score-=count(array_filter($modules,function($v){return !$v;}))*8;
    $score-=count(array_filter($crons,function($v,$name)use($cron_required){return !empty($cron_required[$name]) && !$v['scheduled'];},ARRAY_FILTER_USE_BOTH))*5;
    if(in_array('error',[hcdecor_health_provider('openai'),hcdecor_health_provider('gemini')],true)) $score-=10;
    if($drive_configured && $drive_test==='error') $score-=10;
    if($auto['failed']>0) $score-=min(15,$auto['failed']*3);
    if($actionable_blocked>0) $score-=min(10,$actionable_blocked*2);
    if($drive_configured && ($backup_ts===0 || $backup_age>129600)) $score-=8;
    if($drive_configured && $project_total>0 && $vault_synced<$project_total) $score-=min(8,$project_total-$vault_synced);
    if($vault_errors>0) $score-=min(10,$vault_errors*2);
    if($vault_stale>0) $score-=min(8,$vault_stale);
    if(!empty($inbox_settings['enabled']) && ($inbox_ts===0 || $inbox_age>1800)) $score-=5;
    if($stale_processing>0) $score-=min(10,$stale_processing*2);
    if($processing_without_token>0) $score-=min(8,$processing_without_token*2);
    if($draft_orphan_claims>0) $score-=min(6,$draft_orphan_claims*2);
    if($oldest_review_age>86400) $score-=5;
    $score=max(0,min(100,$score));

    return [
        'schema'=>'hcdecor.health.v1',
        'generated_at'=>current_time('mysql'),
        'site'=>home_url('/'),
        'score'=>$score,
        'state'=>$score>=90?'healthy':($score>=70?'attention':'action_required'),
        'sync'=>[
            'version'=>(string)get_option('hcdecor_sync_version',''),
            'last'=>(string)get_option('hcdecor_sync_last',''),
            'changed'=>(int)get_option('hcdecor_sync_last_changed',0),
            'error'=>(string)get_option('hcdecor_sync_last_error','')
        ],
        'modules'=>$modules,
        'cron'=>$crons,
        'ai'=>[
            'openai'=>hcdecor_health_provider('openai'),
            'gemini'=>hcdecor_health_provider('gemini')
        ],
        'drive'=>[
            'configured'=>$drive_configured,
            'status'=>$drive_configured?($drive_test?:'configured'):'off',
            'email'=>(string)get_option('hcdecor_drive_connected_email','')
        ],
        'project_vault'=>[
            'ready'=>function_exists('hcdecor_project_vault_save'),
            'total_projects'=>$project_total,
            'synced_projects'=>$vault_synced,
            'pending_projects'=>max(0,$project_total-$vault_synced),
            'error_projects'=>$vault_errors,
            'retrying_projects'=>$vault_retrying,
            'stale_projects'=>$vault_stale,
            'stale_scan_count'=>$vault_stale_scanned,
            'stale_scan_limited'=>$vault_synced>$vault_stale_scanned
        ],
        'inbox'=>[
            'settings'=>$inbox_settings,
            'last_at'=>$inbox_last,
            'age_seconds'=>$inbox_age,
            'fresh'=>$inbox_ts>0 && $inbox_age<=1800,
            'last_result'=>(array)get_option('hcdecor_drive_inbox_last_result',[]),
            'error'=>(string)get_option('hcdecor_drive_inbox_last_error','')
        ],
        'automation'=>[
            'enabled'=>!empty($auto_settings['enabled']),
            'social_enabled'=>!empty($auto_settings['social_enabled']),
            'queue'=>$auto,
            'actionable_blocked'=>$actionable_blocked,
            'recovered_24h'=>$auto_recovered_24h,
            'recovery_reasons_24h'=>$auto_recovery_reasons,
            'settings_last_change'=>(array)get_option('hcdecor_automation_settings_last_change',[]),
            'settings_cleanup_limited'=>!empty(((array)get_option('hcdecor_automation_settings_last_change',[]))['cleanup_limited'])
        ],
        'content_queue'=>$queue,
        'stale_processing'=>$stale_processing,
        'worker'=>[
            'processing_without_token'=>$processing_without_token,
            'draft_orphan_claims'=>$draft_orphan_claims,
            'locks_expiring_2m'=>$processing_lock_expiring,
            'recovered_24h'=>$recovered_24h,
            'recovery_reasons_24h'=>$recovery_reasons
        ],
        'review_oldest_age_seconds'=>$oldest_review_age,
        'auto_repair'=>[
            'at'=>(string)($last_auto_repair['at']??''),
            'age_seconds'=>$last_auto_repair_ts?max(0,current_time('timestamp')-$last_auto_repair_ts):null,
            'schedules'=>(array)($last_auto_repair['schedules']??[])
        ],
        'last_outcomes'=>[
            'project_vault_bulk'=>$vault_bulk,
            'project_vault_bulk_at'=>$vault_bulk_at,
            'drive_inbox'=>$inbox_result,
            'drive_inbox_unlinked'=>$inbox_unlinked
        ],
        'throughput_7d'=>[
            'created'=>$created_7d,
            'published'=>$published_7d,
            'output_ratio'=>$created_7d>0?(int)round(($published_7d/$created_7d)*100):0,
            'publish_rate'=>$created_7d>0?(int)round(($published_7d/$created_7d)*100):0
        ],
        'bridge'=>['ready'=>$bridge!==''],
        'publisher'=>['ready'=>function_exists('hcdecor_publish_job_to_web') && post_type_exists('hc_content_job') && post_type_exists('hc_project')],
        'restore'=>[
            'ready'=>function_exists('hcdecor_restore_apply'),
            'last'=>(array)get_option('hcdecor_restore_last_result',[]),
            'error'=>(string)get_option('hcdecor_restore_last_error','')
        ],
        'activity'=>[
            'backup_running'=>$backup_running,
            'project_vault_retrying'=>$vault_retrying,
            'backup_retrying'=>$backup_retry
        ],
        'backup'=>[
            'last_at'=>$backup_last,
            'age_seconds'=>$backup_age,
            'fresh'=>$backup_ts>0 && $backup_age<=129600,
            'retry_count'=>$backup_retry,
            'next_scheduled'=>$backup_cron,
            'running'=>$backup_running,
            'error'=>(string)get_option('hcdecor_backup_last_error',''),
            'ready'=>function_exists('hcdecor_backup_save') && $drive_configured
        ],
        'issues'=>$issues
    ];
}

function hcdecor_health_auto_repair_schedules(){
    $last=(int)get_option('hcdecor_health_auto_repair_at',0);
    if($last && time()-$last<3600) return false;
    $crons=hcdecor_health_crons();
    $auto=function_exists('hcdecor_auto_settings')?(array)hcdecor_auto_settings():[];
    $inbox=function_exists('hcdecor_drive_inbox_settings')?(array)hcdecor_drive_inbox_settings():[];
    $required=['background_sync'=>true,'ai_worker'=>true,'backup'=>true,'health_report'=>true];
    $required['automation']=!isset($auto['enabled']) || !empty($auto['enabled']);
    $required['evergreen']=!empty($auto['evergreen_enabled']);
    $required['drive_inbox']=!isset($inbox['enabled']) || !empty($inbox['enabled']);
    $missing=array_filter($crons,function($v,$name)use($required){ return !empty($required[$name]) && empty($v['scheduled']); },ARRAY_FILTER_USE_BOTH);
    foreach($crons as $name=>$x){
        if(isset($required[$name]) && empty($required[$name]) && !empty($x['scheduled'])){
            $hook=['automation'=>'hcdecor_automation_tick','evergreen'=>'hcdecor_evergreen_tick','drive_inbox'=>'hcdecor_drive_inbox_tick'][$name]??'';
            if($hook) wp_clear_scheduled_hook($hook);
        }
    }
    if(!$missing) return false;
    $names=array_keys($missing);
    hcdecor_health_repair_schedules($required);
    update_option('hcdecor_health_auto_repair_at',time(),false);
    update_option('hcdecor_health_auto_repair_last',['at'=>current_time('mysql'),'schedules'=>$names],false);
    return true;
}

function hcdecor_health_repair_schedules($required=null){
    if(!is_array($required)) $required=['background_sync'=>true,'ai_worker'=>true,'automation'=>true,'evergreen'=>true,'drive_inbox'=>true,'backup'=>true,'health_report'=>true];
    if(!empty($required['background_sync']) && !wp_next_scheduled('hcdecor_background_sync')) wp_schedule_event(time()+60,'hcdecor_5min','hcdecor_background_sync');
    if(!empty($required['ai_worker']) && !wp_next_scheduled('hcdecor_ai_worker_tick')) wp_schedule_event(time()+30,'hcdecor_1min','hcdecor_ai_worker_tick');
    if(!empty($required['automation']) && !wp_next_scheduled('hcdecor_automation_tick')) wp_schedule_event(time()+20,'hcdecor_1min','hcdecor_automation_tick');
    if(!empty($required['evergreen']) && !wp_next_scheduled('hcdecor_evergreen_tick')) wp_schedule_event(time()+300,'hcdecor_daily','hcdecor_evergreen_tick');
    if(!empty($required['drive_inbox']) && !wp_next_scheduled('hcdecor_drive_inbox_tick')) wp_schedule_event(time()+120,'hcdecor_5min','hcdecor_drive_inbox_tick');
    if(!empty($required['backup']) && !wp_next_scheduled('hcdecor_backup_daily')) wp_schedule_event(time()+900,'daily','hcdecor_backup_daily');
    if(!empty($required['health_report']) && !wp_next_scheduled('hcdecor_health_daily_report')) wp_schedule_event(time()+600,'daily','hcdecor_health_daily_report');
    return hcdecor_health_snapshot();
}

add_action('init',function(){
    if(!wp_next_scheduled('hcdecor_health_daily_report')) wp_schedule_event(time()+600,'daily','hcdecor_health_daily_report');
},70);

add_action('hcdecor_health_daily_report',function(){
    $snap=hcdecor_health_snapshot();
    update_option('hcdecor_health_last_snapshot',$snap,false);
    if(function_exists('hcdecor_drive_configured') && hcdecor_drive_configured() && function_exists('hcdecor_drive_save_json')){
        hcdecor_drive_save_json(
            'HEALTH-'.gmdate('Y-m-d').'.json',
            $snap,
            'reports'
        );
    }
});

add_action('admin_post_hcdecor_health_check',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_health_check');
    $snap=hcdecor_health_snapshot();
    update_option('hcdecor_health_last_snapshot',$snap,false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-system-health&checked=1')); exit;
});

add_action('init',function(){ hcdecor_health_auto_repair_schedules(); },95);

add_action('admin_post_hcdecor_health_repair',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_health_repair');
    $snap=hcdecor_health_repair_schedules();
    update_option('hcdecor_health_last_snapshot',$snap,false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-system-health&repaired=1')); exit;
});

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','System Health','System Health','manage_options','hcdecor-system-health','hcdecor_system_health_page',7);
},30);

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/health',[
        'methods'=>'GET',
        'permission_callback'=>function(){
            return function_exists('hcdecor_ops_bridge_auth')?hcdecor_ops_bridge_auth():false;
        },
        'callback'=>function(){return rest_ensure_response(hcdecor_health_snapshot());}
    ]);
});

function hcdecor_system_health_page(){
    if(!current_user_can('manage_options')) return;
    $h=hcdecor_health_snapshot();
    $score=(int)$h['score'];
    $state=$h['state'];
    ?>
    <div class="wrap hch" style="max-width:1400px">
      <style>
      .hch-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:14px 0}
      .hch-card{background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:14px}
      .hch-card strong.big{font-size:28px;display:block}.hch-ok{color:#177245}.hch-warn{color:#996800}.hch-bad{color:#b32d2e}
      .hch-cols{display:grid;grid-template-columns:1fr 1fr;gap:14px}.hch-list{margin:0}.hch-list li{padding:7px 0;border-bottom:1px solid #eee}
      .hch-actions{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}
      @media(max-width:900px){.hch-grid{grid-template-columns:1fr 1fr}.hch-cols{grid-template-columns:1fr}}
      </style>
      <h1>HCDecor System Health</h1>
      <p>Production readiness · AI · Drive · Queue · Cron · Sync · Automation.</p>
      <?php if(isset($_GET['checked'])||isset($_GET['repaired'])):?><div class="notice notice-success inline"><p>Health status đã cập nhật.</p></div><?php endif;?>
      <div class="hch-actions">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
          <input type="hidden" name="action" value="hcdecor_health_check"><?php wp_nonce_field('hcdecor_health_check');?>
          <button class="button button-primary">Run Health Check</button>
        </form>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
          <input type="hidden" name="action" value="hcdecor_health_repair"><?php wp_nonce_field('hcdecor_health_repair');?>
          <button class="button">Repair Schedules</button>
        </form>
        <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-ai-workspace'));?>">AI Workspace</a>
      </div>

      <div class="hch-grid">
        <div class="hch-card"><strong class="big <?php echo $score>=90?'hch-ok':($score>=70?'hch-warn':'hch-bad');?>"><?php echo $score;?>%</strong>System Health<br><small><?php echo esc_html(strtoupper($state));?></small></div>
        <div class="hch-card"><strong class="big"><?php echo array_sum((array)$h['content_queue']);?></strong>Content Jobs<br><small>Review: <?php echo (int)$h['content_queue']['review'];?> · Failed: <?php echo (int)$h['content_queue']['failed'];?></small></div>
        <div class="hch-card"><strong class="big"><?php echo (int)$h['automation']['queue']['queued']+(int)$h['automation']['queue']['scheduled'];?></strong>Automation Pending<br><small>Failed: <?php echo (int)$h['automation']['queue']['failed'];?> · Blocked: <?php echo (int)$h['automation']['queue']['blocked'];?></small></div>
        <div class="hch-card"><strong class="big"><?php echo esc_html($h['sync']['version']?:'—');?></strong>Sync Version<br><small><?php echo esc_html($h['sync']['last']?:'Never');?></small></div>
      </div>

      <div class="hch-cols">
        <section class="hch-card"><h2>Core Services</h2><ul class="hch-list">
          <li>OpenAI: <strong><?php echo esc_html(strtoupper($h['ai']['openai']));?></strong></li>
          <li>Gemini: <strong><?php echo esc_html(strtoupper($h['ai']['gemini']));?></strong></li>
          <li>Drive Vault: <strong><?php echo esc_html(strtoupper($h['drive']['status']));?></strong></li>
          <li>Agent Bridge: <strong><?php echo $h['bridge']['ready']?'READY':'MISSING';?></strong></li>
          <li>Web Publisher: <strong><?php echo $h['publisher']['ready']?'READY':'MISSING';?></strong></li>
          <li>Social outbound: <strong><?php echo $h['automation']['social_enabled']?'ON':'OFF';?></strong></li>
        </ul></section>

        <section class="hch-card"><h2>Schedulers</h2><ul class="hch-list">
          <?php foreach($h['cron'] as $name=>$x):?>
            <li><?php echo esc_html($name);?>: <strong><?php echo $x['scheduled']?'READY':'MISSING';?></strong><?php if($x['next']):?> · <?php echo esc_html(wp_date('d/m H:i',$x['next']));?><?php endif;?></li>
          <?php endforeach;?>
        </ul></section>

        <section class="hch-card"><h2>Modules</h2><ul class="hch-list">
          <?php foreach($h['modules'] as $name=>$ok):?><li><?php echo esc_html($name);?>: <strong><?php echo $ok?'OK':'MISSING';?></strong></li><?php endforeach;?>
        </ul></section>

        <section class="hch-card"><h2>Issues</h2>
          <?php if(!$h['issues']):?><p class="hch-ok"><strong>No blocking issues detected.</strong></p>
          <?php else:?><ul class="hch-list"><?php foreach($h['issues'] as $issue):?><li class="hch-bad"><?php echo esc_html($issue);?></li><?php endforeach;?></ul><?php endif;?>
          <p><small>Daily health snapshot sẽ tự lưu vào Drive / REPORTS khi Drive đã kết nối.</small></p>
        </section>
      </div>
    </div><?php
}
