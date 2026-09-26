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

function hcdecor_backup_project($p){
    return [
        'id'=>(int)$p->ID,
        'title'=>$p->post_title,
        'status'=>$p->post_status,
        'excerpt'=>$p->post_excerpt,
        'content'=>$p->post_content,
        'modified'=>$p->post_modified,
        'gallery'=>(array)get_post_meta($p->ID,'hc_project_gallery',true),
        'gallery_legacy'=>(array)get_post_meta($p->ID,'hc_gallery_ids',true),
        'thumbnail_id'=>(int)get_post_thumbnail_id($p->ID),
        'seo_meta'=>(string)get_post_meta($p->ID,'hc_seo_meta',true),
        'client'=>(string)get_post_meta($p->ID,'hc_client',true),
        'location'=>(string)get_post_meta($p->ID,'hc_location',true),
        'year'=>(string)get_post_meta($p->ID,'hc_year',true),
        'summary'=>(string)get_post_meta($p->ID,'hc_summary',true),
        'drive_project_file_id'=>(string)get_post_meta($p->ID,'hc_drive_project_file_id',true),
        'drive_project_url'=>(string)get_post_meta($p->ID,'hc_drive_project_url',true)
    ];
}

function hcdecor_backup_job($p){
    $fields=[];
    if(function_exists('hcdecor_ops_fields')){
        foreach(hcdecor_ops_fields() as $k) $fields[$k]=get_post_meta($p->ID,'hc_'.$k,true);
    }
    return [
        'id'=>(int)$p->ID,
        'title'=>$p->post_title,
        'brief'=>$p->post_content,
        'modified'=>$p->post_modified,
        'status'=>(string)get_post_meta($p->ID,'hc_agent_status',true),
        'project_id'=>(int)get_post_meta($p->ID,'hc_project_id',true),
        'channels'=>(array)get_post_meta($p->ID,'hc_channels',true),
        'media_ids'=>(array)get_post_meta($p->ID,'hc_media_ids',true),
        'cover_id'=>(int)get_post_meta($p->ID,'hc_cover_id',true),
        'ai_provider'=>(string)get_post_meta($p->ID,'hc_ai_provider',true),
        'ai_model'=>(string)get_post_meta($p->ID,'hc_ai_model',true),
        'content'=>$fields,
        'workflow_log'=>(array)get_post_meta($p->ID,'hc_workflow_log',true),
        'drive_file_id'=>(string)get_post_meta($p->ID,'hc_drive_job_file_id',true),
        'drive_url'=>(string)get_post_meta($p->ID,'hc_drive_job_url',true)
    ];
}

function hcdecor_backup_media($p){
    $id=(int)$p->ID;
    return [
        'id'=>$id,
        'title'=>$p->post_title,
        'caption'=>$p->post_excerpt,
        'description'=>$p->post_content,
        'mime'=>(string)get_post_mime_type($id),
        'alt'=>(string)get_post_meta($id,'_wp_attachment_image_alt',true),
        'drive_file_id'=>(string)get_post_meta($id,'hc_drive_file_id',true),
        'drive_url'=>(string)get_post_meta($id,'hc_drive_url',true),
        'ai_summary'=>(string)get_post_meta($id,'hc_ai_summary',true),
        'ai_alt'=>(string)get_post_meta($id,'hc_ai_alt',true),
        'ai_caption'=>(string)get_post_meta($id,'hc_ai_caption',true),
        'ai_tags'=>(array)get_post_meta($id,'hc_ai_tags',true),
        'ai_visual_type'=>(string)get_post_meta($id,'hc_ai_visual_type',true),
        'cover_score'=>(int)get_post_meta($id,'hc_ai_cover_score',true)
    ];
}

function hcdecor_backup_snapshot(){
    $projects=get_posts(['post_type'=>'hc_project','post_status'=>['publish','draft','private'],'numberposts'=>-1,'orderby'=>'ID','order'=>'ASC']);
    $jobs=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>-1,'orderby'=>'ID','order'=>'ASC']);
    $media=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>-1,'orderby'=>'ID','order'=>'ASC']);

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
            'sync_version'=>(string)get_option('hcdecor_sync_version','')
        ],
        'settings'=>[
            'ai_primary'=>(string)get_option('hcdecor_ai_primary','auto'),
            'openai_model'=>(string)get_option('hcdecor_ai_openai_model',''),
            'gemini_model'=>(string)get_option('hcdecor_ai_gemini_model',''),
            'active_prompt'=>(string)get_option('hcdecor_drive_active_prompt',''),
            'active_prompt_title'=>(string)get_option('hcdecor_drive_active_prompt_title',''),
            'active_prompt_file'=>(string)get_option('hcdecor_drive_active_prompt_file',''),
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
    if(!function_exists('hcdecor_drive_configured') || !hcdecor_drive_configured()) return new WP_Error('drive','Drive Vault chưa kết nối.');
    if(!function_exists('hcdecor_drive_multipart')) return new WP_Error('drive','Drive Vault unavailable.');
    $snap=hcdecor_backup_snapshot();
    $name='HCDECOR-BACKUP-'.gmdate('Ymd-His').'.json';
    $json=wp_json_encode($snap,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    $r=hcdecor_drive_multipart('',$name,'application/json',$json,hcdecor_backup_folder_id());
    if(is_wp_error($r)){
        update_option('hcdecor_backup_last_error',$r->get_error_message(),false);
        return $r;
    }
    update_option('hcdecor_backup_last_at',current_time('mysql'),false);
    update_option('hcdecor_backup_last_file_id',sanitize_text_field((string)($r['id']??'')),false);
    update_option('hcdecor_backup_last_url',esc_url_raw((string)($r['webViewLink']??'')),false);
    update_option('hcdecor_backup_last_counts',$snap['counts'],false);
    delete_option('hcdecor_backup_last_error');
    return $r;
}

add_action('init',function(){
    if(!wp_next_scheduled('hcdecor_backup_daily')) wp_schedule_event(time()+900,'daily','hcdecor_backup_daily');
},75);

add_action('hcdecor_backup_daily',function(){
    if(function_exists('hcdecor_drive_configured') && hcdecor_drive_configured()) hcdecor_backup_save();
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
                'last_at'=>(string)get_option('hcdecor_backup_last_at',''),
                'last_file_id'=>(string)get_option('hcdecor_backup_last_file_id',''),
                'last_url'=>(string)get_option('hcdecor_backup_last_url',''),
                'counts'=>(array)get_option('hcdecor_backup_last_counts',[]),
                'error'=>(string)get_option('hcdecor_backup_last_error',''),
                'next'=>(int)(wp_next_scheduled('hcdecor_backup_daily')?:0)
            ]);
        }
    ]);
});

function hcdecor_backup_page(){
    if(!current_user_can('manage_options')) return;
    $last=(string)get_option('hcdecor_backup_last_at','');
    $url=(string)get_option('hcdecor_backup_last_url','');
    $counts=(array)get_option('hcdecor_backup_last_counts',[]);
    $error=(string)get_option('hcdecor_backup_last_error','');
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
