<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor HUB V2 Dashboard
 * Production command center for the full HCDecor workflow.
 */

function hcdecor_hub_dashboard_counts(){
    $projects=wp_count_posts('hc_project');
    $jobs=post_type_exists('hc_content_job')?wp_count_posts('hc_content_job'):null;
    $media=wp_count_attachments();

    $job_status=['review'=>0,'approved'=>0,'published_web'=>0,'failed'=>0,'processing'=>0,'draft'=>0];
    foreach(array_keys($job_status) as $status){
        $q=new WP_Query([
            'post_type'=>'hc_content_job','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids',
            'meta_key'=>'hc_agent_status','meta_value'=>$status
        ]);
        $job_status[$status]=(int)$q->found_posts;
    }

    $auto=['queued'=>0,'scheduled'=>0,'running'=>0,'failed'=>0,'blocked'=>0];
    if(post_type_exists('hc_automation_task')){
        foreach(array_keys($auto) as $status){
            $q=new WP_Query([
                'post_type'=>'hc_automation_task','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids',
                'meta_key'=>'hc_auto_status','meta_value'=>$status
            ]);
            $auto[$status]=(int)$q->found_posts;
        }
    }

    return [
        'projects_publish'=>(int)($projects->publish??0),
        'projects_draft'=>(int)($projects->draft??0),
        'media'=>(int)($media->inherit??0),
        'jobs'=>(int)($jobs->publish??0),
        'job_status'=>$job_status,
        'automation'=>$auto
    ];
}

function hcdecor_hub_dashboard_state(){
    $health=function_exists('hcdecor_health_snapshot')?hcdecor_health_snapshot():[];
    $counts=hcdecor_hub_dashboard_counts();
    $drive=function_exists('hcdecor_drive_configured')&&hcdecor_drive_configured();
    $automation=function_exists('hcdecor_auto_settings')?hcdecor_auto_settings():[];
    $inbox=function_exists('hcdecor_drive_inbox_settings')?hcdecor_drive_inbox_settings():[];
    $pv=(int)(new WP_Query([
        'post_type'=>'hc_project','post_status'=>['publish','draft','private'],'posts_per_page'=>1,'fields'=>'ids',
        'meta_key'=>'hc_drive_project_file_id'
    ]))->found_posts;

    return [
        'health'=>$health,
        'counts'=>$counts,
        'drive'=>$drive,
        'drive_test'=>(string)get_option('hcdecor_drive_test_status',''),
        'automation'=>$automation,
        'inbox'=>$inbox,
        'project_vault_synced'=>$pv,
        'sync_version'=>(string)get_option('hcdecor_sync_version',''),
        'sync_last'=>(string)get_option('hcdecor_sync_last',''),
        'backup_last'=>(string)get_option('hcdecor_backup_last_at',''),
        'inbox_last'=>(string)get_option('hcdecor_drive_inbox_last_at',''),
        'inbox_result'=>(array)get_option('hcdecor_drive_inbox_last_result',[])
    ];
}

add_action('admin_post_hcdecor_hub_safe_maintenance',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_hub_safe_maintenance');

    $result=['sync'=>null,'schedules'=>null,'health'=>null,'time'=>current_time('mysql')];

    if(function_exists('hcdecor_run_background_sync')){
        $result['sync']=(bool)hcdecor_run_background_sync();
    }
    if(function_exists('hcdecor_health_repair_schedules')){
        $result['schedules']=hcdecor_health_repair_schedules();
    }
    if(function_exists('hcdecor_health_snapshot')){
        $result['health']=hcdecor_health_snapshot();
        update_option('hcdecor_health_last_snapshot',$result['health'],false);
    }

    update_option('hcdecor_hub_last_maintenance',$result,false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-hub&maintenance=1')); exit;
});


add_action('admin_post_hcdecor_hub_sync_projects',function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    check_admin_referer('hcdecor_hub_sync_projects');
    $result=function_exists('hcdecor_project_vault_sync_all')?hcdecor_project_vault_sync_all(100):new WP_Error('vault','Project Vault unavailable.');
    if(is_wp_error($result)) update_option('hcdecor_hub_action_error',$result->get_error_message(),false);
    else delete_option('hcdecor_hub_action_error');
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-hub&hub_action=projects')); exit;
});

add_action('admin_post_hcdecor_hub_run_inbox',function(){
    if(!current_user_can('upload_files')) wp_die('Forbidden');
    check_admin_referer('hcdecor_hub_run_inbox');
    $result=function_exists('hcdecor_drive_inbox_run')?hcdecor_drive_inbox_run():new WP_Error('inbox','Drive Inbox unavailable.');
    if(is_wp_error($result)) update_option('hcdecor_hub_action_error',$result->get_error_message(),false);
    else delete_option('hcdecor_hub_action_error');
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-hub&hub_action=inbox')); exit;
});



add_action('admin_post_hcdecor_hub_backup_now',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_hub_backup_now');
    $result=function_exists('hcdecor_backup_save')?hcdecor_backup_save():new WP_Error('backup','Backup unavailable.');
    if(is_wp_error($result)) update_option('hcdecor_hub_action_error',$result->get_error_message(),false);
    else delete_option('hcdecor_hub_action_error');
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-hub&hub_action=backup')); exit;
});

function hcdecor_hub_dashboard_badge($state){
    $state=(string)$state;
    if(in_array($state,['ok','healthy','connected','ready'],true)) return 'ok';
    if(in_array($state,['error','failed','action_required'],true)) return 'bad';
    return 'warn';
}

function hcdecor_hub_dashboard_attention($s){
    $c=(array)($s['counts']??[]);
    $h=(array)($s['health']??[]);
    $items=[];
    $seen=[];
    $add=function($level,$label,$url) use (&$items,&$seen){
        $key=strtolower(trim((string)$label));
        if($key==='' || isset($seen[$key])) return;
        $seen[$key]=true;
        $items[]=['level'=>$level,'label'=>(string)$label,'url'=>$url];
    };
    foreach((array)($h['issues']??[]) as $issue){
        $label=(string)$issue;
        if(strpos($label,'Automation failed:')===0 || strpos($label,'Automation blocked:')===0) continue;
        $add('bad',$label,admin_url('admin.php?page=hcdecor-system-health'));
    }
    $failed=(int)($c['job_status']['failed']??0);
    if($failed>0) $add('bad',$failed.' content job(s) failed',admin_url('admin.php?page=hcdecor-content-operations'));
    $blocked=(int)($c['automation']['blocked']??0);
    $auto_failed=(int)($c['automation']['failed']??0);
    if($blocked>0) $add('warn',$blocked.' automation task(s) blocked',admin_url('admin.php?page=hcdecor-automation'));
    if($auto_failed>0) $add('bad',$auto_failed.' automation task(s) failed',admin_url('admin.php?page=hcdecor-automation'));
    if(!empty($s['inbox']['enabled']) && !empty($h['inbox']['error'])) $add('bad','Drive Inbox: '.(string)$h['inbox']['error'],admin_url('admin.php?page=hcdecor-drive-inbox'));
    if(!empty($h['backup']['error'])) $add('bad','Backup: '.(string)$h['backup']['error'],admin_url('admin.php?page=hcdecor-data-backups'));
    if(!empty($h['restore']['error'])) $add('bad','Restore: '.(string)$h['restore']['error'],admin_url('admin.php?page=hcdecor-restore-center'));
    $total_projects=(int)($c['projects_publish']??0)+(int)($c['projects_draft']??0);
    $pending=max(0,$total_projects-(int)($s['project_vault_synced']??0));
    if($pending>0) $add('warn',$pending.' project(s) pending Project Vault sync',admin_url('admin.php?page=hcdecor-project-vault'));
    return array_slice($items,0,8);
}

function hcdecor_hub_dashboard_readiness($s){
    $h=(array)($s['health']??[]);
    $checks=[
        'Code sync'=>!empty($s['sync_version']),
        'System health'=>(int)($h['score']??0)>=90,
        'Drive Vault'=>!empty($s['drive']) && (string)($s['drive_test']??'')==='ok',
        'AI provider'=>in_array((string)($h['ai']['openai']??'off'),['ok','configured'],true) || in_array((string)($h['ai']['gemini']??'off'),['ok','configured'],true),
        'Web publisher'=>!empty($h['publisher']['ready']),
        'Backup'=>!empty($h['backup']['ready']),
        'Agent Bridge'=>!empty($h['bridge']['ready'])
    ];
    $ready=count(array_filter($checks));
    return ['checks'=>$checks,'ready'=>$ready,'total'=>count($checks),'percent'=>(int)round(($ready/max(1,count($checks)))*100)];
}

function hcdecor_hub_dashboard_page(){
    if(!current_user_can('edit_posts')) return;
    $s=hcdecor_hub_dashboard_state();
    $c=$s['counts'];
    $h=(array)$s['health'];
    $health_score=(int)($h['score']??0);
    $health_state=(string)($h['state']??'unknown');
    $jobs=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>7,'orderby'=>'modified','order'=>'DESC']);
    $projects=get_posts(['post_type'=>'hc_project','post_status'=>['publish','draft'],'numberposts'=>7,'orderby'=>'modified','order'=>'DESC']);
    $social=!empty($s['automation']['social_enabled']);
    $attention=hcdecor_hub_dashboard_attention($s);
    $readiness=hcdecor_hub_dashboard_readiness($s);
    ?>
    <div class="wrap hchub">
      <style>
      .hchub{max-width:1500px}.hchub *{box-sizing:border-box}
      .hchub-head{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;margin:16px 0}
      .hchub-head h1{margin:0;font-size:28px}.hchub-sub{margin:6px 0;color:#646970}
      .hchub-actions{display:flex;gap:8px;flex-wrap:wrap}
      .hchub-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px;margin:14px 0}
      .hchub-card{background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:15px}
      .hchub-card .num{font-size:27px;font-weight:800;line-height:1.05}.hchub-card small{color:#646970}
      .hchub-main{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(340px,.65fr);gap:14px}
      .hchub-panel{background:#fff;border:1px solid #dcdcde;border-radius:14px;overflow:hidden}
      .hchub-panel h2{font-size:15px;margin:0;padding:14px 16px;border-bottom:1px solid #eee}
      .hchub-body{padding:16px}.hchub-flow{display:flex;gap:7px;flex-wrap:wrap}.hchub-step{padding:8px 11px;border-radius:999px;background:#f6f7f7;border:1px solid #ddd;font-weight:600}
      .hchub-services{display:grid;grid-template-columns:repeat(3,1fr);gap:9px}.hchub-service{border:1px solid #eee;border-radius:11px;padding:11px}
      .hchub-badge{display:inline-block;padding:3px 7px;border-radius:999px;font-size:10px;font-weight:800;background:#f0f0f1}
      .hchub-badge.ok{background:#dff3e4;color:#176c2f}.hchub-badge.warn{background:#fff0c2;color:#7a5b00}.hchub-badge.bad{background:#fce2e2;color:#9c1c1c}
      .hchub-list a{display:block;text-decoration:none;color:#1d2327;padding:10px 0;border-top:1px solid #eee}.hchub-list a:first-child{border-top:0}
      .hchub-list small{display:block;color:#646970;margin-top:2px}.hchub-quick{display:grid;grid-template-columns:1fr 1fr;gap:8px}
      .hchub-quick .button{min-height:40px;display:flex;align-items:center;justify-content:center;text-align:center}
      .hchub-safe{border-left:4px solid #72aee6;background:#f0f6fc;padding:11px;margin-top:12px}
      .hchub-section{margin-top:14px}.hchub-ready{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}.hchub-ready-item{display:flex;justify-content:space-between;gap:8px;padding:9px 10px;border:1px solid #eee;border-radius:10px}.hchub-attention a{display:flex;align-items:center;gap:8px;text-decoration:none;color:#1d2327;padding:9px 0;border-top:1px solid #eee}.hchub-attention a:first-child{border-top:0}.hchub-attention .hchub-badge{flex:0 0 auto}
      @media(max-width:1200px){.hchub-kpis{grid-template-columns:repeat(3,1fr)}.hchub-services{grid-template-columns:repeat(2,1fr)}}
      @media(max-width:900px){.hchub-main{grid-template-columns:1fr}.hchub-head{display:block}.hchub-actions{margin-top:10px}}
      @media(max-width:600px){.hchub{margin-right:10px}.hchub-kpis{grid-template-columns:1fr 1fr}.hchub-services{grid-template-columns:1fr}.hchub-quick{grid-template-columns:1fr}.hchub-flow{overflow:auto;flex-wrap:nowrap;padding-bottom:4px}.hchub-step{white-space:nowrap}.hchub .button{min-height:44px}}
      </style>

      <div class="hchub-head">
        <div>
          <h1>HCDecor HUB V2</h1>
          <p class="hchub-sub">Project → Media → Drive → AI → Review → Publish → Automation → Backup</p>
        </div>
        <div class="hchub-actions">
          <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-ai-workspace'));?>">+ AI Workspace</a>
          <?php if(current_user_can('manage_options')):?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" style="margin:0">
            <input type="hidden" name="action" value="hcdecor_hub_safe_maintenance"><?php wp_nonce_field('hcdecor_hub_safe_maintenance');?>
            <button class="button">Safe Maintenance</button>
          </form><?php endif;?>
        </div>
      </div>

      <?php if(isset($_GET['maintenance'])):?><div class="notice notice-success inline"><p>Safe Maintenance đã chạy: sync + repair schedules + health snapshot.</p></div><?php endif;?>
      <?php if(isset($_GET['hub_action'])): $hub_error=(string)get_option('hcdecor_hub_action_error','');?><div class="notice <?php echo $hub_error?'notice-error':'notice-success';?> inline"><p><?php echo esc_html($hub_error?:'Safe operation completed.');?></p></div><?php endif;?>

      <div class="hchub-kpis">
        <div class="hchub-card"><div class="num"><?php echo (int)$c['projects_publish'];?></div><strong>Projects Live</strong><br><small><?php echo (int)$c['projects_draft'];?> draft</small></div>
        <div class="hchub-card"><div class="num"><?php echo (int)$c['media'];?></div><strong>Media</strong><br><small>WordPress library</small></div>
        <div class="hchub-card"><div class="num"><?php echo (int)$c['jobs'];?></div><strong>Content Jobs</strong><br><small><?php echo (int)$c['job_status']['review'];?> review</small></div>
        <div class="hchub-card"><div class="num"><?php echo (int)($c['automation']['queued']+$c['automation']['scheduled']);?></div><strong>Automation</strong><br><small><?php echo (int)$c['automation']['failed'];?> failed</small></div>
        <div class="hchub-card"><div class="num"><?php echo $health_score;?>%</div><strong>System Health</strong><br><span class="hchub-badge <?php echo esc_attr(hcdecor_hub_dashboard_badge($health_state));?>"><?php echo esc_html(strtoupper($health_state));?></span></div>
        <div class="hchub-card"><div class="num"><?php echo (int)$s['project_vault_synced'];?></div><strong>Project Vault</strong><br><small>Drive manifests</small></div>
      </div>

      <div class="hchub-flow">
        <?php foreach(['1. Project','2. Drive Inbox','3. Media AI','4. AI Content','5. Review','6. Publish Web','7. Drive Vault','8. Backup'] as $step):?>
          <span class="hchub-step"><?php echo esc_html($step);?></span>
        <?php endforeach;?>
      </div>

      <div class="hchub-main hchub-section">
        <div>
          <section class="hchub-panel">
            <h2>SYSTEM SERVICES</h2>
            <div class="hchub-body hchub-services">
              <?php
              $services=[
                ['OpenAI',(string)($h['ai']['openai']??'off')],
                ['Gemini',(string)($h['ai']['gemini']??'off')],
                ['Drive Vault',$s['drive']?($s['drive_test']==='ok'?'ok':'configured'):'off'],
                ['Drive Inbox',!empty($s['inbox']['enabled'])?'ready':'off'],
                ['Automation',!empty($s['automation']['enabled'])?'ready':'off'],
                ['Web Publisher',!empty($h['publisher']['ready'])?'ready':'off'],
                ['Backup',!empty($h['backup']['ready'])?'ready':'off'],
                ['Restore',!empty($h['restore']['ready'])?'ready':'off'],
                ['Social outbound',$social?'connected':'off']
              ];
              foreach($services as $x): $badge=hcdecor_hub_dashboard_badge($x[1]);?>
                <div class="hchub-service"><strong><?php echo esc_html($x[0]);?></strong><br><span class="hchub-badge <?php echo esc_attr($badge);?>"><?php echo esc_html(strtoupper($x[1]));?></span></div>
              <?php endforeach;?>
            </div>
          </section>

          <section class="hchub-panel hchub-section">
            <h2>ATTENTION QUEUE · <?php echo count($attention);?></h2>
            <div class="hchub-body hchub-attention">
              <?php if(!$attention):?><p><span class="hchub-badge ok">CLEAR</span> Không có cảnh báo vận hành cần xử lý.</p><?php else: foreach($attention as $item):?>
                <a href="<?php echo esc_url($item['url']);?>"><span class="hchub-badge <?php echo esc_attr($item['level']);?>"><?php echo $item['level']==='bad'?'ACTION':'CHECK';?></span><span><?php echo esc_html($item['label']);?></span></a>
              <?php endforeach; endif;?>
            </div>
          </section>

          <section class="hchub-panel hchub-section">
            <h2>RECENT CONTENT JOBS</h2>
            <div class="hchub-body hchub-list">
              <?php foreach($jobs as $j): $st=(string)get_post_meta($j->ID,'hc_agent_status',true); $pid=(int)get_post_meta($j->ID,'hc_project_id',true);?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-content-operations&job='.$j->ID));?>">
                  <strong>#<?php echo $j->ID;?> · <?php echo esc_html($j->post_title);?></strong>
                  <small><?php echo esc_html(strtoupper($st?:'draft'));?> · <?php echo esc_html(get_the_title($pid));?></small>
                </a>
              <?php endforeach; if(!$jobs):?><p>Chưa có Content Job.</p><?php endif;?>
            </div>
          </section>

          <section class="hchub-panel hchub-section">
            <h2>RECENT PROJECTS</h2>
            <div class="hchub-body hchub-list">
              <?php foreach($projects as $p): $fid=(string)get_post_meta($p->ID,'hc_drive_project_file_id',true);?>
                <a href="<?php echo esc_url(get_edit_post_link($p->ID));?>">
                  <strong>#<?php echo $p->ID;?> · <?php echo esc_html($p->post_title);?></strong>
                  <small><?php echo esc_html(strtoupper($p->post_status));?> · Project Vault <?php echo $fid?'SYNCED':'PENDING';?></small>
                </a>
              <?php endforeach; if(!$projects):?><p>Chưa có Project.</p><?php endif;?>
            </div>
          </section>
        </div>

        <aside>
          <section class="hchub-panel">
            <h2>QUICK OPERATIONS</h2>
            <div class="hchub-body hchub-quick">
              <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-ai-workspace'));?>">Create AI Job</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-review'));?>">Review Center</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-drive-inbox'));?>">Drive Inbox</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-media'));?>">Media Manager</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-project-vault'));?>">Project Vault</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-drive-vault'));?>">Drive Vault</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-automation'));?>">Automation HUB</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-system-health'));?>">System Health</a>
              <?php if(current_user_can('upload_files')):?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="hcdecor_hub_run_inbox"><?php wp_nonce_field('hcdecor_hub_run_inbox');?><button class="button">Run Drive Inbox</button></form><?php endif;?>
              <?php if(current_user_can('edit_posts')):?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="hcdecor_hub_sync_projects"><?php wp_nonce_field('hcdecor_hub_sync_projects');?><button class="button">Sync Project Vault</button></form><?php endif;?>
              <?php if(current_user_can('manage_options')):?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="hcdecor_hub_backup_now"><?php wp_nonce_field('hcdecor_hub_backup_now');?><button class="button">Backup Now</button></form><?php endif;?>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-data-backups'));?>">Data Backups</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-restore-center'));?>">Restore Center</a>
            </div>
          </section>

          <section class="hchub-panel hchub-section">
            <h2>OPERATIONAL READINESS · <?php echo (int)$readiness['percent'];?>%</h2>
            <div class="hchub-body hchub-ready">
              <?php foreach($readiness['checks'] as $label=>$ok):?>
                <div class="hchub-ready-item"><span><?php echo esc_html($label);?></span><span class="hchub-badge <?php echo $ok?'ok':'warn';?>"><?php echo $ok?'READY':'CHECK';?></span></div>
              <?php endforeach;?>
            </div>
          </section>

          <section class="hchub-panel hchub-section">
            <h2>DATA STATUS</h2>
            <div class="hchub-body">
              <p><strong>Sync version:</strong> <?php echo esc_html($s['sync_version']?:'—');?></p>
              <p><strong>Last code sync:</strong> <?php echo esc_html($s['sync_last']?:'—');?></p>
              <p><strong>Last Drive Inbox:</strong> <?php echo esc_html($s['inbox_last']?:'—');?></p>
              <p><strong>Last backup:</strong> <?php echo esc_html($s['backup_last']?:'—');?></p>
              <p><strong>Review:</strong> <?php echo (int)$c['job_status']['review'];?> · <strong>Failed jobs:</strong> <?php echo (int)$c['job_status']['failed'];?></p>
              <p><strong>Automation failed:</strong> <?php echo (int)$c['automation']['failed'];?> · <strong>Blocked:</strong> <?php echo (int)$c['automation']['blocked'];?></p>
              <div class="hchub-safe"><strong>External social publishing:</strong> <?php echo $social?'ON':'OFF';?><br><small>Safe Maintenance không bật social outbound và không thay credentials.</small></div>
            </div>
          </section>
        </aside>
      </div>
    </div>
    <?php
}
