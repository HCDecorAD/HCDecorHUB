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
        if($type==='social_publish' && empty($settings['social_enabled'])) continue;
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
    $bridge=(string)get_option('hcdecor_bridge_token','');
    $actionable_blocked=hcdecor_health_actionable_blocked_count();
    $backup_last=(string)get_option('hcdecor_backup_last_at','');
    $backup_ts=$backup_last!==''?strtotime($backup_last):0;
    $backup_age=$backup_ts?max(0,current_time('timestamp')-$backup_ts):null;
    $inbox_last=(string)get_option('hcdecor_drive_inbox_last_at','');
    $inbox_ts=$inbox_last!==''?strtotime($inbox_last):0;
    $inbox_age=$inbox_ts?max(0,current_time('timestamp')-$inbox_ts):null;
    $stale_processing=0;
    $oldest_review_age=0;
    $review_ids=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>100,'fields'=>'ids','meta_key'=>'hc_agent_status','meta_value'=>'review']);
    foreach($review_ids as $rid){
        $modified=strtotime((string)get_post_field('post_modified',$rid))?:0;
        if($modified) $oldest_review_age=max($oldest_review_age,max(0,current_time('timestamp')-$modified));
    }
    $processing_ids=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>50,'fields'=>'ids','meta_key'=>'hc_agent_status','meta_value'=>'processing']);
    $now=time();
    foreach($processing_ids as $pid){
        $lock=(int)get_post_meta($pid,'hc_agent_lock_until',true);
        $claimed=strtotime((string)get_post_meta($pid,'hc_agent_claimed_at',true))?:0;
        if(($lock>0 && $lock<$now) || ($lock<=0 && $claimed>0 && $claimed<($now-900))) $stale_processing++;
    }
    $project_total=(int)(new WP_Query(['post_type'=>'hc_project','post_status'=>['publish','draft','private'],'posts_per_page'=>1,'fields'=>'ids']))->found_posts;
    $vault_synced=(int)(new WP_Query(['post_type'=>'hc_project','post_status'=>['publish','draft','private'],'posts_per_page'=>1,'meta_key'=>'hc_drive_project_file_id','fields'=>'ids']))->found_posts;
    $vault_errors=(int)(new WP_Query(['post_type'=>'hc_project','post_status'=>['publish','draft','private'],'posts_per_page'=>1,'meta_key'=>'hc_drive_project_error','meta_compare'=>'EXISTS','fields'=>'ids']))->found_posts;
    $vault_stale=0;
    $vault_ids=get_posts(['post_type'=>'hc_project','post_status'=>['publish','draft','private'],'numberposts'=>100,'fields'=>'ids','meta_key'=>'hc_drive_project_file_id','meta_compare'=>'EXISTS']);
    foreach($vault_ids as $pid){
        $synced=strtotime((string)get_post_meta($pid,'hc_drive_project_synced_at',true))?:0;
        $modified=strtotime((string)get_post_field('post_modified',$pid))?:0;
        if($modified>0 && ($synced===0 || $modified>$synced+5)) $vault_stale++;
    }

    $issues=[];
    foreach($modules as $name=>$ok) if(!$ok) $issues[]='Missing module: '.$name;
    foreach($crons as $name=>$x) if(!$x['scheduled']) $issues[]='Cron missing: '.$name;
    if(hcdecor_health_provider('openai')==='error') $issues[]='OpenAI test error';
    if(hcdecor_health_provider('gemini')==='error') $issues[]='Gemini test error';
    if($drive_configured && $drive_test==='error') $issues[]='Google Drive connection error';
    if($auto['failed']>0) $issues[]='Automation failed: '.$auto['failed'];
    if($actionable_blocked>0) $issues[]='Automation blocked: '.$actionable_blocked;
    if($bridge==='') $issues[]='Agent Bridge token missing';
    if($drive_configured && $backup_ts===0) $issues[]='Backup has never completed';
    elseif($drive_configured && $backup_age>129600) $issues[]='Backup is stale (>36h)';
    if($drive_configured && $project_total>0 && $vault_synced<$project_total) $issues[]='Project Vault pending: '.($project_total-$vault_synced);
    if($vault_errors>0) $issues[]='Project Vault errors: '.$vault_errors;
    if($vault_stale>0) $issues[]='Project Vault stale: '.$vault_stale;
    $inbox_settings=function_exists('hcdecor_drive_inbox_settings')?hcdecor_drive_inbox_settings():[];
    if(!empty($inbox_settings['enabled']) && $inbox_ts===0) $issues[]='Drive Inbox has never completed';
    elseif(!empty($inbox_settings['enabled']) && $inbox_age>1800) $issues[]='Drive Inbox is stale (>30 min)';
    if($stale_processing>0) $issues[]='Stale processing jobs: '.$stale_processing;
    if($oldest_review_age>86400) $issues[]='Review queue oldest item >24h';

    $score=100;
    $score-=count(array_filter($modules,function($v){return !$v;}))*8;
    $score-=count(array_filter($crons,function($v){return !$v['scheduled'];}))*5;
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
            'stale_projects'=>$vault_stale
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
            'actionable_blocked'=>$actionable_blocked
        ],
        'content_queue'=>$queue,
        'stale_processing'=>$stale_processing,
        'review_oldest_age_seconds'=>$oldest_review_age,
        'bridge'=>['ready'=>$bridge!==''],
        'publisher'=>['ready'=>function_exists('hcdecor_publish_job_to_web') && post_type_exists('hc_content_job') && post_type_exists('hc_project')],
        'restore'=>[
            'ready'=>function_exists('hcdecor_restore_apply'),
            'last'=>(array)get_option('hcdecor_restore_last_result',[]),
            'error'=>(string)get_option('hcdecor_restore_last_error','')
        ],
        'backup'=>[
            'last_at'=>$backup_last,
            'age_seconds'=>$backup_age,
            'fresh'=>$backup_ts>0 && $backup_age<=129600,
            'error'=>(string)get_option('hcdecor_backup_last_error',''),
            'ready'=>function_exists('hcdecor_backup_save') && $drive_configured
        ],
        'issues'=>$issues
    ];
}

function hcdecor_health_repair_schedules(){
    if(!wp_next_scheduled('hcdecor_background_sync')) wp_schedule_event(time()+60,'hcdecor_5min','hcdecor_background_sync');
    if(!wp_next_scheduled('hcdecor_ai_worker_tick')) wp_schedule_event(time()+30,'hcdecor_1min','hcdecor_ai_worker_tick');
    if(!wp_next_scheduled('hcdecor_automation_tick')) wp_schedule_event(time()+20,'hcdecor_1min','hcdecor_automation_tick');
    if(!wp_next_scheduled('hcdecor_evergreen_tick')) wp_schedule_event(time()+300,'hcdecor_daily','hcdecor_evergreen_tick');
    if(!wp_next_scheduled('hcdecor_drive_inbox_tick')) wp_schedule_event(time()+120,'hcdecor_5min','hcdecor_drive_inbox_tick');
    if(!wp_next_scheduled('hcdecor_backup_daily')) wp_schedule_event(time()+900,'daily','hcdecor_backup_daily');
    if(!wp_next_scheduled('hcdecor_health_daily_report')) wp_schedule_event(time()+600,'daily','hcdecor_health_daily_report');
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
