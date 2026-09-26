<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor Drive Inbox
 * Google Drive / 03_MEDIA/INPUT -> WordPress Media Manager.
 * Duplicate-safe by Drive File ID. Optional AI analysis is OFF by default.
 */

function hcdecor_drive_inbox_settings(){
    $defaults=[
        'enabled'=>true,
        'auto_analyze'=>false,
        'auto_link_project'=>true,
        'limit'=>12
    ];
    $saved=get_option('hcdecor_drive_inbox_settings',[]);
    return wp_parse_args(is_array($saved)?$saved:[],$defaults);
}

function hcdecor_drive_inbox_log($event,$data=[]){
    $log=(array)get_option('hcdecor_drive_inbox_log',[]);
    $log[]=[
        'time'=>current_time('mysql'),
        'event'=>sanitize_key($event),
        'data'=>map_deep((array)$data,'sanitize_text_field')
    ];
    if(count($log)>100) $log=array_slice($log,-100);
    update_option('hcdecor_drive_inbox_log',$log,false);
}

function hcdecor_drive_inbox_existing_attachment($drive_id){
    $drive_id=sanitize_text_field((string)$drive_id);
    if($drive_id==='') return 0;
    $found=get_posts([
        'post_type'=>'attachment',
        'post_status'=>'inherit',
        'numberposts'=>1,
        'fields'=>'ids',
        'meta_key'=>'hc_drive_file_id',
        'meta_value'=>$drive_id
    ]);
    return $found?(int)$found[0]:0;
}

function hcdecor_drive_inbox_project_from_name($name){
    $name=(string)$name;
    if(!preg_match('/(?:^|[^A-Z0-9])(?:P|PROJECT)[-_ ]?(\d+)(?:[^0-9]|$)/i',$name,$m)) return 0;
    $id=(int)$m[1];
    return $id && get_post_type($id)==='hc_project' ? $id : 0;
}

function hcdecor_drive_inbox_link_project($attachment_id,$project_id){
    $attachment_id=(int)$attachment_id;
    $project_id=(int)$project_id;
    if(!$attachment_id || get_post_type($attachment_id)!=='attachment') return false;
    if(!$project_id || get_post_type($project_id)!=='hc_project') return false;

    update_post_meta($attachment_id,'hc_project_id',$project_id);

    $gallery=(array)get_post_meta($project_id,'hc_project_gallery',true);
    $gallery=array_values(array_unique(array_filter(array_map('intval',array_merge($gallery,[$attachment_id])))));
    update_post_meta($project_id,'hc_project_gallery',$gallery);
    update_post_meta($project_id,'hc_gallery_ids',$gallery);

    if(!has_post_thumbnail($project_id) && wp_attachment_is_image($attachment_id)){
        set_post_thumbnail($project_id,$attachment_id);
    }
    do_action('hcdecor_project_data_changed',$project_id);
    return true;
}

function hcdecor_drive_inbox_scan($limit=null){
    $settings=hcdecor_drive_inbox_settings();
    if(empty($settings['enabled'])) return ['scanned'=>0,'imported'=>0,'skipped'=>0,'failed'=>0,'linked'=>0,'analyze_queued'=>0,'disabled'=>true];
    if(!function_exists('hcdecor_drive_configured') || !hcdecor_drive_configured()) return new WP_Error('drive','Google Drive chưa kết nối.');
    if(!function_exists('hcdecor_drive_list') || !function_exists('hcdecor_drive_import_media')) return new WP_Error('drive','Drive media bridge unavailable.');

    $folders=hcdecor_drive_folders();
    $folder=(string)($folders['media_input']??'');
    if($folder==='') return new WP_Error('folder','Drive Media Input chưa cấu hình.');

    $limit=$limit===null?(int)$settings['limit']:(int)$limit;
    $limit=max(1,min(50,$limit));
    $files=hcdecor_drive_list($folder,$limit);
    if(is_wp_error($files)){
        update_option('hcdecor_drive_inbox_last_error',$files->get_error_message(),false);
        return $files;
    }

    $result=['scanned'=>0,'imported'=>0,'skipped'=>0,'failed'=>0,'linked'=>0,'analyze_queued'=>0,'disabled'=>false];

    foreach($files as $file){
        $result['scanned']++;
        $id=sanitize_text_field((string)($file['id']??''));
        $name=sanitize_text_field((string)($file['name']??''));
        $mime=sanitize_text_field((string)($file['mimeType']??''));

        if($id==='' || (strpos($mime,'image/')!==0 && strpos($mime,'video/')!==0)){
            $result['skipped']++;
            continue;
        }

        $existing=hcdecor_drive_inbox_existing_attachment($id);
        if($existing){
            $result['skipped']++;
            continue;
        }

        $imported=hcdecor_drive_import_media($id);
        if(is_wp_error($imported)){
            $result['failed']++;
            hcdecor_drive_inbox_log('import_failed',['drive_id'=>$id,'name'=>$name,'error'=>$imported->get_error_message()]);
            continue;
        }

        $attachment_id=(int)$imported;
        update_post_meta($attachment_id,'hc_drive_inbox_imported_at',current_time('mysql'));
        update_post_meta($attachment_id,'hc_drive_inbox_source_name',$name);
        $result['imported']++;

        $project_id=0;
        if(!empty($settings['auto_link_project'])){
            $project_id=hcdecor_drive_inbox_project_from_name($name);
            if($project_id && hcdecor_drive_inbox_link_project($attachment_id,$project_id)) $result['linked']++;
        }

        if(!empty($settings['auto_analyze']) && wp_attachment_is_image($attachment_id) && function_exists('wp_schedule_single_event')){
            wp_schedule_single_event(time()+15,'hcdecor_drive_inbox_analyze_media',[$attachment_id]);
            $result['analyze_queued']++;
        }

        hcdecor_drive_inbox_log('imported',[
            'drive_id'=>$id,
            'attachment_id'=>$attachment_id,
            'name'=>$name,
            'project_id'=>$project_id
        ]);
    }

    update_option('hcdecor_drive_inbox_last_at',current_time('mysql'),false);
    update_option('hcdecor_drive_inbox_last_result',$result,false);
    delete_option('hcdecor_drive_inbox_last_error');
    return $result;
}

add_action('hcdecor_drive_inbox_analyze_media',function($attachment_id){
    $attachment_id=(int)$attachment_id;
    if($attachment_id && function_exists('hcdecor_media_ai_analyze')){
        $r=hcdecor_media_ai_analyze($attachment_id);
        hcdecor_drive_inbox_log(is_wp_error($r)?'analyze_failed':'analyzed',[
            'attachment_id'=>$attachment_id,
            'message'=>is_wp_error($r)?$r->get_error_message():'ok'
        ]);
    }
},10,1);

add_action('init',function(){
    if(!wp_next_scheduled('hcdecor_drive_inbox_tick')) wp_schedule_event(time()+120,'hcdecor_5min','hcdecor_drive_inbox_tick');
},76);

add_action('hcdecor_drive_inbox_tick',function(){
    $s=hcdecor_drive_inbox_settings();
    if(!empty($s['enabled']) && function_exists('hcdecor_drive_configured') && hcdecor_drive_configured()){
        hcdecor_drive_inbox_scan();
    }
});

add_action('admin_post_hcdecor_drive_inbox_settings',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_drive_inbox_settings');

    $settings=[
        'enabled'=>!empty($_POST['enabled']),
        'auto_analyze'=>!empty($_POST['auto_analyze']),
        'auto_link_project'=>!empty($_POST['auto_link_project']),
        'limit'=>max(1,min(50,(int)($_POST['limit']??12)))
    ];
    update_option('hcdecor_drive_inbox_settings',$settings,false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-inbox&saved=1')); exit;
});

add_action('admin_post_hcdecor_drive_inbox_scan',function(){
    if(!current_user_can('upload_files')) wp_die('Forbidden');
    check_admin_referer('hcdecor_drive_inbox_scan');
    $r=hcdecor_drive_inbox_scan();
    if(is_wp_error($r)){
        update_option('hcdecor_drive_inbox_last_error',$r->get_error_message(),false);
        wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-inbox&failed=1')); exit;
    }
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-inbox&scanned=1')); exit;
});

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','Drive Inbox','Drive Inbox','upload_files','hcdecor-drive-inbox','hcdecor_drive_inbox_page',4);
},27);

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/drive/inbox/status',[
        'methods'=>'GET',
        'permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(){
            return rest_ensure_response([
                'settings'=>hcdecor_drive_inbox_settings(),
                'last_at'=>(string)get_option('hcdecor_drive_inbox_last_at',''),
                'last_result'=>(array)get_option('hcdecor_drive_inbox_last_result',[]),
                'last_error'=>(string)get_option('hcdecor_drive_inbox_last_error',''),
                'next'=>(int)(wp_next_scheduled('hcdecor_drive_inbox_tick')?:0)
            ]);
        }
    ]);
    register_rest_route('hcdecor/v1','/drive/inbox/scan',[
        'methods'=>'POST',
        'permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(){
            $r=hcdecor_drive_inbox_scan();
            return is_wp_error($r)?$r:rest_ensure_response($r);
        }
    ]);
});

function hcdecor_drive_inbox_page(){
    if(!current_user_can('upload_files')) return;
    $s=hcdecor_drive_inbox_settings();
    $last=(array)get_option('hcdecor_drive_inbox_last_result',[]);
    $last_at=(string)get_option('hcdecor_drive_inbox_last_at','');
    $error=(string)get_option('hcdecor_drive_inbox_last_error','');
    $next=(int)(wp_next_scheduled('hcdecor_drive_inbox_tick')?:0);
    $log=array_reverse((array)get_option('hcdecor_drive_inbox_log',[]));
    $folders=function_exists('hcdecor_drive_folders')?hcdecor_drive_folders():[];
    $connected=function_exists('hcdecor_drive_configured')&&hcdecor_drive_configured();
    ?>
    <div class="wrap hcdi" style="max-width:1300px">
      <style>
      .hcdi-grid{display:grid;grid-template-columns:390px 1fr;gap:14px}.hcdi-card{background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px}
      .hcdi-kpi{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:12px 0}.hcdi-kpi>div{background:#f6f7f7;border-radius:10px;padding:10px}
      .hcdi-log{padding:8px 0;border-top:1px solid #eee}.hcdi-log:first-child{border-top:0}.hcdi-actions{display:flex;gap:8px;flex-wrap:wrap}
      @media(max-width:800px){.hcdi-grid{grid-template-columns:1fr}.hcdi-kpi{grid-template-columns:1fr 1fr}.hcdi .button{min-height:42px}}
      </style>
      <h1>HCDecor Drive Inbox</h1>
      <p>Drive <strong>03_MEDIA / INPUT</strong> → WordPress Media Manager. Dedupe bằng Drive File ID.</p>

      <?php if(isset($_GET['saved'])||isset($_GET['scanned'])):?><div class="notice notice-success inline"><p>Drive Inbox đã cập nhật.</p></div><?php endif;?>
      <?php if(isset($_GET['failed'])):?><div class="notice notice-error inline"><p><?php echo esc_html($error?:'Drive Inbox scan failed.');?></p></div><?php endif;?>

      <div class="hcdi-kpi">
        <div><strong><?php echo $connected?'CONNECTED':'OFF';?></strong><br>Drive</div>
        <div><strong><?php echo (int)($last['imported']??0);?></strong><br>Imported last scan</div>
        <div><strong><?php echo (int)($last['linked']??0);?></strong><br>Auto-linked Project</div>
        <div><strong><?php echo (int)($last['failed']??0);?></strong><br>Failed</div>
      </div>

      <div class="hcdi-grid">
        <section class="hcdi-card">
          <h2>Inbox Automation</h2>
          <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
            <input type="hidden" name="action" value="hcdecor_drive_inbox_settings"><?php wp_nonce_field('hcdecor_drive_inbox_settings');?>
            <p><label><input type="checkbox" name="enabled" <?php checked($s['enabled']);?>> Auto Import mỗi 5 phút</label></p>
            <p><label><input type="checkbox" name="auto_link_project" <?php checked($s['auto_link_project']);?>> Auto-link Project theo tên file <code>P123_...</code> hoặc <code>PROJECT-123_...</code></label></p>
            <p><label><input type="checkbox" name="auto_analyze" <?php checked($s['auto_analyze']);?>> Auto AI Analyze ảnh sau import</label><br><small>Mặc định OFF để tránh dùng API ngoài ý muốn.</small></p>
            <p>Giới hạn mỗi scan <input type="number" name="limit" min="1" max="50" value="<?php echo (int)$s['limit'];?>" style="width:80px"> file</p>
            <button class="button button-primary">Lưu Drive Inbox</button>
          </form>

          <hr>
          <div class="hcdi-actions">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
              <input type="hidden" name="action" value="hcdecor_drive_inbox_scan"><?php wp_nonce_field('hcdecor_drive_inbox_scan');?>
              <button class="button button-primary">Scan Now</button>
            </form>
            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-media'));?>">Media Manager</a>
            <?php if(!empty($folders['media_input'])):?><a class="button" target="_blank" rel="noopener" href="<?php echo esc_url('https://drive.google.com/drive/folders/'.$folders['media_input']);?>">Open Drive INPUT</a><?php endif;?>
          </div>

          <p><strong>Last:</strong> <?php echo esc_html($last_at?:'Chưa scan');?></p>
          <p><strong>Next:</strong> <?php echo $next?esc_html(wp_date('d/m/Y H:i',$next)):'—';?></p>
          <?php if($error):?><p style="color:#b32d2e"><strong>Error:</strong> <?php echo esc_html($error);?></p><?php endif;?>
        </section>

        <section class="hcdi-card">
          <h2>Recent Inbox Activity</h2>
          <?php if(!$log):?><p>Chưa có activity.</p><?php endif;?>
          <?php foreach(array_slice($log,0,40) as $row): $d=(array)($row['data']??[]);?>
            <div class="hcdi-log">
              <strong><?php echo esc_html(strtoupper((string)($row['event']??'')));?></strong>
              · <?php echo esc_html((string)($row['time']??''));?>
              <?php if(!empty($d['name'])):?><br><?php echo esc_html($d['name']);?><?php endif;?>
              <?php if(!empty($d['attachment_id'])):?> · WP #<?php echo (int)$d['attachment_id'];?><?php endif;?>
              <?php if(!empty($d['project_id'])):?> · Project #<?php echo (int)$d['project_id'];?><?php endif;?>
              <?php if(!empty($d['error'])):?><br><span style="color:#b32d2e"><?php echo esc_html($d['error']);?></span><?php endif;?>
            </div>
          <?php endforeach;?>
        </section>
      </div>
    </div><?php
}
