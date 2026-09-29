<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor Data Backup
 * Non-secret system snapshot -> Google Drive / 09_AUTOMATION/BACKUPS.
 */

function hcdecor_backup_folder_id(){
    $folders=function_exists('hcdecor_drive_folders')?hcdecor_drive_folders():[];
    return (string)($folders['backups']??'1-tNi0yUg9mNQuabzIRRYyigFM899EfHp');
}

function hcdecor_backup_object_identity($post_id){
    $identity=hcdecor_backup_clip(get_post_meta((int)$post_id,'hc_backup_identity',true),100);
    if($identity===''){
        $identity=wp_generate_uuid4();
        add_post_meta((int)$post_id,'hc_backup_identity',$identity,true);
        $identity=hcdecor_backup_clip(get_post_meta((int)$post_id,'hc_backup_identity',true),100);
    }
    return sanitize_text_field($identity);
}

function hcdecor_backup_clip($value,$limit){
    if(!is_scalar($value)) return '';$value=(string)$value;
    return function_exists('mb_substr')?mb_substr($value,0,$limit):substr($value,0,$limit);
}

function hcdecor_backup_workflow_log($value){
    $out=[];
    foreach(array_slice((array)$value,-100) as $entry){
        if(!is_array($entry)) continue;
        $out[]=['time'=>hcdecor_backup_clip($entry['time']??'',64),'event'=>sanitize_key((string)($entry['event']??'')),'note'=>hcdecor_backup_clip($entry['note']??'',500),'user'=>(int)($entry['user']??0)];
    }
    return $out;
}

function hcdecor_backup_tags($value){
    $out=[];
    foreach(array_slice((array)$value,0,30) as $tag){$tag=hcdecor_backup_clip($tag,100);if($tag!=='')$out[]=$tag;}
    return array_values(array_unique($out));
}

function hcdecor_backup_project($p){
    return [
        'backup_identity'=>hcdecor_backup_object_identity($p->ID),
        'id'=>(int)$p->ID,
        'title'=>hcdecor_backup_clip($p->post_title,500),
        'status'=>sanitize_key($p->post_status),
        'excerpt'=>hcdecor_backup_clip($p->post_excerpt,5000),
        'content'=>hcdecor_backup_clip($p->post_content,100000),
        'modified'=>$p->post_modified,
        'gallery'=>array_slice((array)get_post_meta($p->ID,'hc_project_gallery',true),0,60),
        'gallery_legacy'=>array_slice((array)get_post_meta($p->ID,'hc_gallery_ids',true),0,60),
        'thumbnail_id'=>(int)get_post_thumbnail_id($p->ID),
        'seo_meta'=>hcdecor_backup_clip(get_post_meta($p->ID,'hc_seo_meta',true),2000),
        'client'=>hcdecor_backup_clip(get_post_meta($p->ID,'hc_client',true),2000),
        'location'=>hcdecor_backup_clip(get_post_meta($p->ID,'hc_location',true),2000),
        'year'=>hcdecor_backup_clip(get_post_meta($p->ID,'hc_year',true),2000),
        'summary'=>hcdecor_backup_clip(get_post_meta($p->ID,'hc_summary',true),10000)
    ];
}

function hcdecor_backup_job($p){
    $fields=[];
    if(function_exists('hcdecor_ops_fields')){
        $limits=['web_title'=>300,'web_intro'=>2000,'web_body'=>50000,'seo_meta'=>2000,'facebook_caption'=>10000,'tiktok_script'=>20000,'youtube_title'=>300,'youtube_description'=>20000];
        foreach(hcdecor_ops_fields() as $k) $fields[$k]=hcdecor_backup_clip(get_post_meta($p->ID,'hc_'.$k,true),(int)($limits[$k]??10000));
    }
    return [
        'backup_identity'=>hcdecor_backup_object_identity($p->ID),
        'id'=>(int)$p->ID,
        'title'=>hcdecor_backup_clip($p->post_title,500),
        'brief'=>hcdecor_backup_clip($p->post_content,20000),
        'modified'=>$p->post_modified,
        'status'=>(string)get_post_meta($p->ID,'hc_agent_status',true),
        'project_id'=>(int)get_post_meta($p->ID,'hc_project_id',true),
        'channels'=>array_values(array_intersect(['web','facebook','tiktok','youtube'],(array)get_post_meta($p->ID,'hc_channels',true))),
        'media_ids'=>array_slice((array)get_post_meta($p->ID,'hc_media_ids',true),0,60),
        'cover_id'=>(int)get_post_meta($p->ID,'hc_cover_id',true),
        'ai_provider'=>sanitize_key((string)get_post_meta($p->ID,'hc_ai_provider',true)),
        'ai_model'=>hcdecor_backup_clip(get_post_meta($p->ID,'hc_ai_model',true),200),
        'content'=>$fields,
        'workflow_log'=>hcdecor_backup_workflow_log(get_post_meta($p->ID,'hc_workflow_log',true))
    ];
}

function hcdecor_backup_media($p){
    $id=(int)$p->ID;
    return [
        'id'=>$id,
        'title'=>hcdecor_backup_clip($p->post_title,500),
        'caption'=>hcdecor_backup_clip($p->post_excerpt,5000),
        'description'=>hcdecor_backup_clip($p->post_content,10000),
        'mime'=>(string)get_post_mime_type($id),
        'alt'=>hcdecor_backup_clip(get_post_meta($id,'_wp_attachment_image_alt',true),1000),
        'drive_file_id'=>hcdecor_backup_clip(get_post_meta($id,'hc_drive_file_id',true),300),
        'ai_summary'=>hcdecor_backup_clip(get_post_meta($id,'hc_ai_summary',true),5000),
        'ai_alt'=>hcdecor_backup_clip(get_post_meta($id,'hc_ai_alt',true),1000),
        'ai_caption'=>hcdecor_backup_clip(get_post_meta($id,'hc_ai_caption',true),5000),
        'ai_tags'=>hcdecor_backup_tags(get_post_meta($id,'hc_ai_tags',true)),
        'ai_visual_type'=>hcdecor_backup_clip(get_post_meta($id,'hc_ai_visual_type',true),1000),
        'cover_score'=>(int)get_post_meta($id,'hc_ai_cover_score',true)
    ];
}

function hcdecor_backup_snapshot(){
    $projects=get_posts(['post_type'=>'hc_project','post_status'=>['publish','draft','private'],'numberposts'=>2001,'orderby'=>'ID','order'=>'ASC']);
    $jobs=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>5001,'orderby'=>'ID','order'=>'ASC']);
    $media=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>10001,'orderby'=>'ID','order'=>'ASC']);
    if(count($projects)>2000 || count($jobs)>5000 || count($media)>10000){
        return new WP_Error('backup_limit','Backup object count exceeds the safe restore limits.');
    }

    $auto=function_exists('hcdecor_auto_settings')?hcdecor_auto_settings():[];
    unset($auto['webhook_url']);

    return [
        'schema'=>'hcdecor.system-backup.v1',
        'created_at'=>current_time('mysql'),
        'site'=>[
            'name'=>get_bloginfo('name'),
            'url'=>home_url('/'),
            'wp_version'=>get_bloginfo('version'),
            'timezone'=>wp_timezone_string(),
            'sync_version'=>hcdecor_backup_clip(get_option('hcdecor_sync_version',''),100)
        ],
        'settings'=>[
            'ai_primary'=>hcdecor_backup_clip(get_option('hcdecor_ai_primary','auto'),50),
            'openai_model'=>hcdecor_backup_clip(get_option('hcdecor_ai_openai_model',''),200),
            'gemini_model'=>hcdecor_backup_clip(get_option('hcdecor_ai_gemini_model',''),200),
            'active_prompt'=>hcdecor_backup_clip(get_option('hcdecor_drive_active_prompt',''),20000),
            'active_prompt_title'=>hcdecor_backup_clip(get_option('hcdecor_drive_active_prompt_title',''),300),
            'active_prompt_file'=>hcdecor_backup_clip(get_option('hcdecor_drive_active_prompt_file',''),300),
            'automation'=>$auto,
            'recipes'=>function_exists('hcdecor_recipes')?hcdecor_recipes():[]
        ],
        'projects'=>array_map('hcdecor_backup_project',$projects),
        'content_jobs'=>array_map('hcdecor_backup_job',$jobs),
        'media_index'=>array_map('hcdecor_backup_media',$media),
        'counts'=>[
            'projects'=>count($projects),
            'jobs'=>count($jobs),
            'media'=>count($media)
        ]
    ];
}

function hcdecor_backup_save(){
    if(get_transient('hcdecor_backup_running')) return new WP_Error('busy','Backup already running.');
    set_transient('hcdecor_backup_running',1,15*MINUTE_IN_SECONDS);
    if(!function_exists('hcdecor_drive_configured') || !hcdecor_drive_configured()){
        delete_transient('hcdecor_backup_running');
        update_option('hcdecor_backup_last_error','Drive Vault chưa kết nối.',false);
        return new WP_Error('drive','Drive Vault chưa kết nối.');
    }
    if(!function_exists('hcdecor_drive_multipart')){
        update_option('hcdecor_backup_last_error','Drive Vault unavailable.',false);
        delete_transient('hcdecor_backup_running');
        return new WP_Error('drive','Drive Vault unavailable.');
    }
    $snap=hcdecor_backup_snapshot();
    if(is_wp_error($snap)){
        update_option('hcdecor_backup_last_error',function_exists('hcdecor_drive_safe_error')?hcdecor_drive_safe_error($snap->get_error_message()):'Backup snapshot failed.',false);
        delete_transient('hcdecor_backup_running');
        return $snap;
    }
    $name='HCDECOR-BACKUP-'.gmdate('Ymd-His').'.json';
    $json=wp_json_encode($snap,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    if(!is_string($json) || $json===''){
        update_option('hcdecor_backup_last_error','Không thể mã hóa backup.',false);
        delete_transient('hcdecor_backup_running');
        return new WP_Error('backup_json','Không thể mã hóa backup.');
    }
    $snap['integrity']=['algorithm'=>'sha256','payload_hash'=>hash('sha256',$json)];
    $json=wp_json_encode($snap,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    if(!is_string($json) || $json===''){
        update_option('hcdecor_backup_last_error','Không thể mã hóa backup integrity.',false);
        delete_transient('hcdecor_backup_running');
        return new WP_Error('backup_json','Không thể mã hóa backup integrity.');
    }
    if(strlen($json)>25*1024*1024){
        update_option('hcdecor_backup_last_error','Backup exceeds the 25 MB restore limit.',false);
        delete_transient('hcdecor_backup_running');
        return new WP_Error('backup_size','Backup exceeds the 25 MB restore limit.');
    }
    $r=hcdecor_drive_multipart('',$name,'application/json',$json,hcdecor_backup_folder_id());
    if(is_wp_error($r)){
        update_option('hcdecor_backup_last_error',function_exists('hcdecor_drive_safe_error')?hcdecor_drive_safe_error($r->get_error_message()):'Drive backup upload failed.',false);
        delete_transient('hcdecor_backup_running');
        return $r;
    }
    update_option('hcdecor_backup_last_at',current_time('mysql'),false);
    update_option('hcdecor_backup_last_file_id',sanitize_text_field((string)($r['id']??'')),false);
    update_option('hcdecor_backup_last_url',esc_url_raw((string)($r['webViewLink']??'')),false);
    update_option('hcdecor_backup_last_counts',$snap['counts'],false);
    delete_option('hcdecor_backup_last_error');
    delete_transient('hcdecor_backup_running');
    return $r;
}

function hcdecor_backup_schedule_enabled(){ return (bool)get_option('hcdecor_backup_schedule_enabled',false); }
add_action('init',function(){
    if(hcdecor_backup_schedule_enabled()){
        if(!wp_next_scheduled('hcdecor_backup_daily')) wp_schedule_event(time()+900,'daily','hcdecor_backup_daily');
    }else{
        wp_clear_scheduled_hook('hcdecor_backup_daily');
        wp_clear_scheduled_hook('hcdecor_backup_retry');
    }
},75);

add_action('hcdecor_backup_daily',function(){
    if(!hcdecor_backup_schedule_enabled()) return;
    if(!function_exists('hcdecor_drive_configured') || !hcdecor_drive_configured()){
        update_option('hcdecor_backup_last_error','Scheduled backup skipped: Drive Vault chưa kết nối.',false);
        delete_option('hcdecor_backup_retry_count');
        return;
    }
    $r=hcdecor_backup_save();
    if(is_wp_error($r)){
        if($r->get_error_code()==='busy') return;
        $attempts=(int)get_option('hcdecor_backup_retry_count',0)+1;
        update_option('hcdecor_backup_retry_count',$attempts,false);
        if($attempts<3 && !wp_next_scheduled('hcdecor_backup_retry')){
            wp_schedule_single_event(time()+min(1800,300*$attempts),'hcdecor_backup_retry');
        }
    }else{
        delete_option('hcdecor_backup_retry_count');
    }
});
add_action('hcdecor_backup_retry',function(){
    if(!hcdecor_backup_schedule_enabled()) return;
    if(!function_exists('hcdecor_drive_configured') || !hcdecor_drive_configured()){
        update_option('hcdecor_backup_last_error','Backup retry skipped: Drive Vault chưa kết nối.',false);
        delete_option('hcdecor_backup_retry_count');
        return;
    }
    $r=hcdecor_backup_save();
    if(is_wp_error($r)){
        if($r->get_error_code()==='busy'){
            if(!wp_next_scheduled('hcdecor_backup_retry')) wp_schedule_single_event(time()+300,'hcdecor_backup_retry');
            return;
        }
        $attempts=(int)get_option('hcdecor_backup_retry_count',0)+1;
        update_option('hcdecor_backup_retry_count',$attempts,false);
        if($attempts<3 && !wp_next_scheduled('hcdecor_backup_retry')) wp_schedule_single_event(time()+min(1800,300*$attempts),'hcdecor_backup_retry');
    }else{
        delete_option('hcdecor_backup_retry_count');
    }
});

add_action('admin_post_hcdecor_backup_now',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_backup_now');
    $r=hcdecor_backup_save();
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-data-backups&'.(is_wp_error($r)?'failed=1':'done=1'))); exit;
});

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','Data Backups','Data Backups','manage_options','hcdecor-data-backups','hcdecor_backup_page',8);
},31);

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/backup/status',[
        'methods'=>'GET',
        'permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(){
            return rest_ensure_response([
                'last_at'=>hcdecor_backup_clip(get_option('hcdecor_backup_last_at',''),64),
                'has_backup_file'=>(bool)get_option('hcdecor_backup_last_file_id',''),
                'counts'=>(array)get_option('hcdecor_backup_last_counts',[]),
                'error'=>hcdecor_backup_clip(get_option('hcdecor_backup_last_error',''),500),
                'next'=>(int)(wp_next_scheduled('hcdecor_backup_daily')?:0)
            ]);
        }
    ]);
});

function hcdecor_backup_page(){
    if(!current_user_can('manage_options')) return;
    $last=hcdecor_backup_clip(get_option('hcdecor_backup_last_at',''),64);
    $url=hcdecor_backup_clip(get_option('hcdecor_backup_last_url',''),2048);
    $counts=(array)get_option('hcdecor_backup_last_counts',[]);
    $error=hcdecor_backup_clip(get_option('hcdecor_backup_last_error',''),500);
    $next=(int)(wp_next_scheduled('hcdecor_backup_daily')?:0);
    $files=[];
    if(function_exists('hcdecor_drive_configured') && hcdecor_drive_configured() && function_exists('hcdecor_drive_list')){
        $files=hcdecor_drive_list(hcdecor_backup_folder_id(),30);
        if(is_wp_error($files)) $files=[];
    }
    ?>
    <div class="wrap" style="max-width:1200px">
      <h1>HCDecor Data Backups</h1>
      <p>Snapshot không chứa API key, OAuth token hoặc secret. DATA gốc/media vẫn nằm trong Drive Vault.</p>
      <?php if(isset($_GET['done'])):?><div class="notice notice-success inline"><p>Backup đã lưu vào Google Drive.</p></div><?php endif;?>
      <?php if(isset($_GET['failed'])):?><div class="notice notice-error inline"><p>Backup thất bại. <?php echo esc_html($error);?></p></div><?php endif;?>
      <div style="display:grid;grid-template-columns:360px 1fr;gap:14px">
        <section style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:18px">
          <h2>Backup Status</h2>
          <p><strong>Last:</strong> <?php echo esc_html($last?:'Chưa có');?></p>
          <p><strong>Next:</strong> <?php echo $next?esc_html(wp_date('d/m/Y H:i',$next)):'—';?></p>
          <?php if($counts):?><p>Projects: <?php echo (int)($counts['projects']??0);?> · Jobs: <?php echo (int)($counts['jobs']??0);?> · Media: <?php echo (int)($counts['media']??0);?></p><?php endif;?>
          <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
            <input type="hidden" name="action" value="hcdecor_backup_now"><?php wp_nonce_field('hcdecor_backup_now');?>
            <button class="button button-primary">Backup Now → Drive</button>
          </form>
          <?php if($url):?><p><a class="button" target="_blank" rel="noopener" href="<?php echo esc_url($url);?>">Open Latest Backup</a></p><?php endif;?>
          <p><a class="button" target="_blank" rel="noopener" href="<?php echo esc_url('https://drive.google.com/drive/folders/'.hcdecor_backup_folder_id());?>">Open BACKUPS Folder</a></p>
          <p><a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-restore-center'));?>">Restore Center</a></p>
        </section>
        <section style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:18px">
          <h2>Recent Backups</h2>
          <?php if(!$files):?><p>Chưa có backup hoặc Drive chưa kết nối.</p><?php else:?>
          <table class="widefat striped"><thead><tr><th>File</th><th>Modified</th><th>Size</th><th></th></tr></thead><tbody>
          <?php foreach($files as $x):?><tr><td><?php echo esc_html($x['name']??'');?></td><td><?php echo esc_html($x['modifiedTime']??'');?></td><td><?php echo esc_html($x['size']??'');?></td><td><?php if(!empty($x['webViewLink'])):?><a class="button button-small" target="_blank" rel="noopener" href="<?php echo esc_url($x['webViewLink']);?>">Open</a><?php endif;?></td></tr><?php endforeach;?>
          </tbody></table><?php endif;?>
        </section>
      </div>
    </div><?php
}
