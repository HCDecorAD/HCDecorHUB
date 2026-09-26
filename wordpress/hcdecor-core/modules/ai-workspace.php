<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor AI Workspace
 * Mobile-first command center:
 * Prompt -> Project -> Media -> AI -> Review -> Drive -> Publish.
 */

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','AI Workspace','AI Workspace','edit_posts','hcdecor-ai-workspace','hcdecor_ai_workspace_page',0);
},19);

add_action('admin_enqueue_scripts',function($hook){
    if(strpos((string)$hook,'hcdecor-ai-workspace')===false) return;
    wp_enqueue_media();
});

function hcdecor_workspace_provider_state($provider){
    if(!function_exists('hcdecor_ai_available')) return 'off';
    if(!hcdecor_ai_available($provider)) return 'off';
    $test=(string)get_option('hcdecor_ai_'.$provider.'_test_status','');
    return $test==='ok'?'ok':($test==='error'?'error':'saved');
}

function hcdecor_workspace_drive_state(){
    if(!function_exists('hcdecor_drive_configured') || !hcdecor_drive_configured()) return 'off';
    return (string)get_option('hcdecor_drive_test_status','')==='ok'?'ok':'saved';
}

function hcdecor_workspace_badge($state){
    $labels=['ok'=>'CONNECTED','saved'=>'CONFIGURED','error'=>'ERROR','off'=>'OFF'];
    return $labels[$state]??strtoupper((string)$state);
}

add_action('admin_post_hcdecor_workspace_create',function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    check_admin_referer('hcdecor_workspace_create');

    $project=(int)($_POST['project_id']??0);
    if(!$project || get_post_type($project)!=='hc_project') wp_die('Invalid project');

    $brief=sanitize_textarea_field(wp_unslash($_POST['brief']??''));
    $media=array_values(array_unique(array_filter(array_map('intval',(array)($_POST['media_ids']??[])))));
    $channels=array_values(array_intersect(['web','facebook','tiktok','youtube'],(array)($_POST['channels']??[])));

    if(!function_exists('hcdecor_agent_create_job')) wp_die('Agent intake unavailable.');
    $job=hcdecor_agent_create_job($project,$media,$brief);
    if(is_wp_error($job)) wp_die($job->get_error_message());

    if($channels) update_post_meta($job,'hc_channels',$channels);

    if(!empty($_POST['save_drive']) && function_exists('hcdecor_drive_configured') && hcdecor_drive_configured()){
        wp_schedule_single_event(time()+90,'hcdecor_workspace_drive_save',[(int)$job]);
    }

    wp_safe_redirect(admin_url('admin.php?page=hcdecor-ai-workspace&job='.(int)$job.'&created=1'));
    exit;
});

add_action('hcdecor_workspace_drive_save',function($job_id){
    if(function_exists('hcdecor_drive_save_job') && function_exists('hcdecor_drive_configured') && hcdecor_drive_configured()){
        hcdecor_drive_save_job((int)$job_id,true);
    }
},10,1);

function hcdecor_ai_workspace_page(){
    if(!current_user_can('edit_posts')) return;

    $projects=get_posts([
        'post_type'=>'hc_project','post_status'=>['publish','draft'],'numberposts'=>100,
        'orderby'=>'modified','order'=>'DESC'
    ]);
    $jobs=get_posts([
        'post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>12,
        'orderby'=>'modified','order'=>'DESC'
    ]);

    $openai=hcdecor_workspace_provider_state('openai');
    $gemini=hcdecor_workspace_provider_state('gemini');
    $drive=hcdecor_workspace_drive_state();
    $automation=function_exists('hcdecor_auto_settings')&&!empty(hcdecor_auto_settings()['enabled'])?'ok':'off';
    $prompt=(string)get_option('hcdecor_drive_active_prompt','');
    $prompt_title=(string)get_option('hcdecor_drive_active_prompt_title','');
    $created=(int)($_GET['job']??0);
    ?>
    <div class="wrap hcaw">
      <style>
        .hcaw{max-width:1420px}.hcaw *{box-sizing:border-box}
        .hcaw-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin:14px 0}
        .hcaw-head h1{margin:0}.hcaw-sub{margin:5px 0;color:#646970}
        .hcaw-flow{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}
        .hcaw-step{background:#fff;border:1px solid #dcdcde;border-radius:999px;padding:8px 12px;font-weight:600}
        .hcaw-grid{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(320px,.75fr);gap:14px}
        .hcaw-card{background:#fff;border:1px solid #dcdcde;border-radius:14px;overflow:hidden}
        .hcaw-card h2{font-size:15px;margin:0;padding:14px 16px;border-bottom:1px solid #eee}
        .hcaw-body{padding:16px}.hcaw label{font-weight:600;display:block;margin:12px 0 5px}
        .hcaw textarea,.hcaw select,.hcaw input[type=text]{width:100%}.hcaw textarea{min-height:110px}
        .hcaw-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
        .hcaw-status{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin:12px 0}
        .hcaw-status>div{background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:12px}
        .hcaw-pill{display:inline-block;border-radius:999px;padding:3px 8px;font-size:11px;font-weight:700;background:#f0f0f1}
        .hcaw-pill.ok{background:#dff3e4;color:#176c2f}.hcaw-pill.error{background:#fce2e2;color:#9c1c1c}
        .hcaw-media{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:7px;margin:10px 0}
        .hcaw-media img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:9px}
        .hcaw-job{display:block;text-decoration:none;color:#1d2327;border-top:1px solid #eee;padding:11px 0}
        .hcaw-job:first-child{border-top:0}.hcaw-job small{display:block;color:#646970;margin-top:2px}
        .hcaw-links{display:grid;grid-template-columns:1fr 1fr;gap:8px}
        .hcaw-links a{text-align:center}
        .hcaw-note{background:#f6f7f7;border:1px solid #dcdcde;border-radius:10px;padding:11px}
        @media(max-width:900px){
          .hcaw-grid{grid-template-columns:1fr}.hcaw-status{grid-template-columns:1fr 1fr}
          .hcaw-media{grid-template-columns:repeat(4,1fr)}
        }
        @media(max-width:600px){
          .hcaw{margin-right:10px}.hcaw-head{display:block}.hcaw-status{grid-template-columns:1fr 1fr}
          .hcaw-flow{overflow:auto;flex-wrap:nowrap;padding-bottom:4px}.hcaw-step{white-space:nowrap}
          .hcaw-links{grid-template-columns:1fr}.hcaw-media{grid-template-columns:repeat(3,1fr)}
          .hcaw .button{min-height:42px;display:inline-flex;align-items:center;justify-content:center}
          .hcaw textarea{font-size:16px}
        }
      </style>

      <div class="hcaw-head">
        <div><h1>HCDecor AI Workspace</h1><p class="hcaw-sub">Prompt → Project → Media → AI → Review → Drive → Publish</p></div>
        <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-drive-vault'));?>">Drive Vault</a>
      </div>

      <?php if(isset($_GET['created'])):?><div class="notice notice-success inline"><p>Đã tạo Content Job #<?php echo $created;?> và đưa vào AI queue.</p></div><?php endif;?>

      <div class="hcaw-status">
        <?php foreach([
          ['OpenAI',$openai],['Gemini',$gemini],['Drive Vault',$drive],['Automation',$automation]
        ] as $x):?>
          <div><strong><?php echo esc_html($x[0]);?></strong><br><span class="hcaw-pill <?php echo esc_attr($x[1]);?>"><?php echo esc_html(hcdecor_workspace_badge($x[1]));?></span></div>
        <?php endforeach;?>
      </div>

      <div class="hcaw-flow">
        <?php foreach(['1. Prompt','2. Project','3. Media','4. AI Generate','5. Review','6. Save Drive','7. Publish'] as $x):?><span class="hcaw-step"><?php echo esc_html($x);?></span><?php endforeach;?>
      </div>

      <div class="hcaw-grid">
        <section class="hcaw-card">
          <h2>CREATE AI JOB</h2>
          <div class="hcaw-body">
            <?php if($prompt):?>
              <div class="hcaw-note"><strong>Active Drive Prompt:</strong> <?php echo esc_html($prompt_title?:'Prompt Vault');?> · sẽ tự đưa vào AI context.</div>
            <?php else:?>
              <div class="hcaw-note">Chưa có Active Drive Prompt. Có thể dùng brief trực tiếp hoặc chọn prompt trong Drive Vault.</div>
            <?php endif;?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" id="hcawForm">
              <input type="hidden" name="action" value="hcdecor_workspace_create"><?php wp_nonce_field('hcdecor_workspace_create');?>

              <label>Project</label>
              <select name="project_id" required>
                <option value="">— Chọn Project —</option>
                <?php foreach($projects as $p):?><option value="<?php echo $p->ID;?>"><?php echo esc_html($p->post_title);?></option><?php endforeach;?>
              </select>

              <label>Media</label>
              <div class="hcaw-media" id="hcawMedia"></div>
              <div id="hcawHidden"></div>
              <button type="button" class="button" id="hcawPick">Chọn / Upload Media</button>

              <label>Yêu cầu cho HUB Agent</label>
              <textarea name="brief" placeholder="Ví dụ: phân tích ảnh, chọn điểm nổi bật, viết nội dung dự án theo phong cách HCDecor, ưu tiên thông tin có cơ sở."></textarea>

              <label>Kênh đầu ra</label>
              <div style="display:flex;gap:12px;flex-wrap:wrap">
                <?php foreach(['web'=>'Web','facebook'=>'Facebook','tiktok'=>'TikTok / Reels','youtube'=>'YouTube'] as $k=>$label):?>
                  <label style="margin:0;font-weight:400"><input type="checkbox" name="channels[]" value="<?php echo esc_attr($k);?>" <?php checked($k==='web');?>> <?php echo esc_html($label);?></label>
                <?php endforeach;?>
              </div>

              <label style="font-weight:400"><input type="checkbox" name="save_drive" value="1" <?php checked($drive==='ok');?>> Auto Save DATA vào Drive Vault sau AI</label>

              <div class="hcaw-actions">
                <button class="button button-primary">Tạo + Gửi HUB Agent</button>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-media'));?>">Media Manager</a>
              </div>
            </form>
          </div>
        </section>

        <aside>
          <section class="hcaw-card" style="margin-bottom:14px">
            <h2>QUICK ACCESS</h2>
            <div class="hcaw-body hcaw-links">
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-review'));?>">Review Center</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-content-operations'));?>">Content Operations</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-automation'));?>">Automation HUB</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-ai-providers'));?>">AI Providers</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-system-health'));?>">System Health</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-drive-inbox'));?>">Drive Inbox</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-data-backups'));?>">Data Backups</a>
              <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-restore-center'));?>">Restore Center</a>
            </div>
          </section>

          <section class="hcaw-card">
            <h2>RECENT JOBS</h2>
            <div class="hcaw-body">
              <?php if(!$jobs):?><p>Chưa có Content Job.</p><?php endif;?>
              <?php foreach($jobs as $j):
                $s=(string)get_post_meta($j->ID,'hc_agent_status',true);
                $pid=(int)get_post_meta($j->ID,'hc_project_id',true);
              ?>
                <a class="hcaw-job" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-content-operations&job='.$j->ID));?>">
                  <strong>#<?php echo $j->ID;?> · <?php echo esc_html($j->post_title);?></strong>
                  <small><?php echo esc_html(strtoupper($s?:'draft'));?> · <?php echo esc_html(get_the_title($pid));?></small>
                </a>
              <?php endforeach;?>
            </div>
          </section>
        </aside>
      </div>

      <script>
      (function(){
        const pick=document.getElementById('hcawPick');
        if(!pick || typeof wp==='undefined' || !wp.media) return;
        let selected=[];
        const grid=document.getElementById('hcawMedia'), hidden=document.getElementById('hcawHidden');
        function draw(items){
          grid.innerHTML='';hidden.innerHTML='';
          items.forEach(function(a){
            selected.push(a.id);
            const img=document.createElement('img');
            img.src=(a.sizes&&a.sizes.medium?a.sizes.medium.url:a.url);
            img.alt='';grid.appendChild(img);
            const h=document.createElement('input');h.type='hidden';h.name='media_ids[]';h.value=a.id;hidden.appendChild(h);
          });
        }
        pick.onclick=function(){
          selected=[];
          const frame=wp.media({title:'HCDecor Media',button:{text:'Dùng media đã chọn'},multiple:true});
          frame.on('select',function(){draw(frame.state().get('selection').toJSON());});
          frame.open();
        };
      })();
      </script>
    </div><?php
}
