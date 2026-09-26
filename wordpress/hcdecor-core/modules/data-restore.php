<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor Restore Center
 * Safe disaster recovery from hcdecor.system-backup.v1.
 * Always creates a fresh safety backup before applying restore.
 */

function hcdecor_restore_read_backup($file_id){
    $file_id=preg_replace('/[^A-Za-z0-9_-]/','',(string)$file_id);
    if(!$file_id) return new WP_Error('file','Backup File ID không hợp lệ.');
    if(!function_exists('hcdecor_drive_download')) return new WP_Error('drive','Drive Vault unavailable.');

    $body=hcdecor_drive_download($file_id);
    if(is_wp_error($body)) return $body;
    if(strlen((string)$body)>25*1024*1024) return new WP_Error('size','Backup vượt giới hạn 25MB.');

    $data=json_decode((string)$body,true);
    if(!is_array($data) || ($data['schema']??'')!=='hcdecor.system-backup.v1'){
        return new WP_Error('schema','Không phải HCDecor system backup hợp lệ.');
    }
    return $data;
}

function hcdecor_restore_media_map($backup){
    $map=[];
    foreach((array)($backup['media_index']??[]) as $m){
        $old=(int)($m['id']??0);
        if(!$old) continue;

        if(get_post_type($old)==='attachment'){
            $map[$old]=$old;
            continue;
        }

        $drive_id=sanitize_text_field((string)($m['drive_file_id']??''));
        if($drive_id!==''){
            $found=get_posts([
                'post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'fields'=>'ids',
                'meta_key'=>'hc_drive_file_id','meta_value'=>$drive_id
            ]);
            if($found){ $map[$old]=(int)$found[0]; continue; }
        }
        $map[$old]=0;
    }
    return $map;
}

function hcdecor_restore_plan($backup){
    $media_map=hcdecor_restore_media_map($backup);
    $projects=['update'=>0,'create'=>0];
    $jobs=['update'=>0,'create'=>0];
    $media=['matched'=>0,'missing'=>0];

    foreach((array)($backup['projects']??[]) as $p){
        $id=(int)($p['id']??0);
        if($id && get_post_type($id)==='hc_project') $projects['update']++;
        else $projects['create']++;
    }

    foreach((array)($backup['content_jobs']??[]) as $j){
        $id=(int)($j['id']??0);
        if($id && get_post_type($id)==='hc_content_job') $jobs['update']++;
        else $jobs['create']++;
    }

    foreach($media_map as $id=>$mapped){
        if($mapped) $media['matched']++; else $media['missing']++;
    }

    return [
        'backup_created_at'=>(string)($backup['created_at']??''),
        'backup_site'=>(string)($backup['site']['url']??''),
        'sync_version'=>(string)($backup['site']['sync_version']??''),
        'projects'=>$projects,
        'jobs'=>$jobs,
        'media'=>$media,
        'settings'=>!empty($backup['settings'])
    ];
}

function hcdecor_restore_map_ids($ids,$map){
    $out=[];
    foreach((array)$ids as $id){
        $old=(int)$id;
        $new=(int)($map[$old]??0);
        if($new) $out[]=$new;
    }
    return array_values(array_unique($out));
}

function hcdecor_restore_projects($backup,$media_map){
    $project_map=[];
    $created=0; $updated=0;

    foreach((array)($backup['projects']??[]) as $p){
        $old=(int)($p['id']??0);
        $existing=$old && get_post_type($old)==='hc_project' ? $old : 0;
        $status=in_array(($p['status']??''),['publish','draft','private'],true)?$p['status']:'draft';

        $post=[
            'post_type'=>'hc_project',
            'post_status'=>$status,
            'post_title'=>sanitize_text_field((string)($p['title']??'Restored Project')),
            'post_excerpt'=>sanitize_textarea_field((string)($p['excerpt']??'')),
            'post_content'=>wp_kses_post((string)($p['content']??''))
        ];
        if($existing) $post['ID']=$existing;

        $r=$existing?wp_update_post($post,true):wp_insert_post($post,true);
        if(is_wp_error($r)) continue;
        $id=(int)$r;
        if($old) $project_map[$old]=$id;
        $existing?$updated++:$created++;

        $gallery=hcdecor_restore_map_ids((array)($p['gallery']??[]),$media_map);
        $legacy=hcdecor_restore_map_ids((array)($p['gallery_legacy']??[]),$media_map);
        update_post_meta($id,'hc_project_gallery',$gallery);
        update_post_meta($id,'hc_gallery_ids',$legacy?:$gallery);

        foreach([
            'hc_seo_meta'=>'seo_meta',
            'hc_client'=>'client',
            'hc_location'=>'location',
            'hc_year'=>'year',
            'hc_summary'=>'summary'
        ] as $meta=>$key){
            if(array_key_exists($key,$p)) update_post_meta($id,$meta,sanitize_textarea_field((string)$p[$key]));
        }

        $thumb_old=(int)($p['thumbnail_id']??0);
        $thumb=(int)($media_map[$thumb_old]??0);
        if($thumb) set_post_thumbnail($id,$thumb);
    }

    return ['map'=>$project_map,'created'=>$created,'updated'=>$updated];
}

function hcdecor_restore_jobs($backup,$project_map,$media_map){
    $created=0; $updated=0;
    $allowed_status=['draft','processing','review','approved','published_web','failed'];

    foreach((array)($backup['content_jobs']??[]) as $j){
        $old=(int)($j['id']??0);
        $existing=$old && get_post_type($old)==='hc_content_job' ? $old : 0;

        $post=[
            'post_type'=>'hc_content_job',
            'post_status'=>'publish',
            'post_title'=>sanitize_text_field((string)($j['title']??'Restored Content Job')),
            'post_content'=>sanitize_textarea_field((string)($j['brief']??''))
        ];
        if($existing) $post['ID']=$existing;

        $r=$existing?wp_update_post($post,true):wp_insert_post($post,true);
        if(is_wp_error($r)) continue;
        $id=(int)$r;
        $existing?$updated++:$created++;

        $old_project=(int)($j['project_id']??0);
        $project=(int)($project_map[$old_project]??0);
        if(!$project && get_post_type($old_project)==='hc_project') $project=$old_project;
        update_post_meta($id,'hc_project_id',$project);

        $status=sanitize_key((string)($j['status']??'draft'));
        update_post_meta($id,'hc_agent_status',in_array($status,$allowed_status,true)?$status:'draft');

        $channels=array_values(array_intersect(['web','facebook','tiktok','youtube'],(array)($j['channels']??[])));
        update_post_meta($id,'hc_channels',$channels);

        $media=hcdecor_restore_map_ids((array)($j['media_ids']??[]),$media_map);
        update_post_meta($id,'hc_media_ids',$media);

        $cover_old=(int)($j['cover_id']??0);
        $cover=(int)($media_map[$cover_old]??0);
        update_post_meta($id,'hc_cover_id',$cover);

        if(function_exists('hcdecor_ops_save_fields')){
            hcdecor_ops_save_fields($id,(array)($j['content']??[]));
        }

        update_post_meta($id,'hc_ai_provider',sanitize_key((string)($j['ai_provider']??'')));
        update_post_meta($id,'hc_ai_model',sanitize_text_field((string)($j['ai_model']??'')));

        if(!empty($j['workflow_log']) && is_array($j['workflow_log'])){
            update_post_meta($id,'hc_workflow_log',map_deep($j['workflow_log'],'sanitize_text_field'));
        }

        if(!empty($j['drive_file_id'])) update_post_meta($id,'hc_drive_job_file_id',sanitize_text_field((string)$j['drive_file_id']));
        if(!empty($j['drive_url'])) update_post_meta($id,'hc_drive_job_url',esc_url_raw((string)$j['drive_url']));
    }

    return ['created'=>$created,'updated'=>$updated];
}

function hcdecor_restore_media_metadata($backup,$media_map){
    $updated=0;
    foreach((array)($backup['media_index']??[]) as $m){
        $old=(int)($m['id']??0);
        $id=(int)($media_map[$old]??0);
        if(!$id || get_post_type($id)!=='attachment') continue;

        wp_update_post([
            'ID'=>$id,
            'post_title'=>sanitize_text_field((string)($m['title']??'')),
            'post_excerpt'=>sanitize_textarea_field((string)($m['caption']??'')),
            'post_content'=>sanitize_textarea_field((string)($m['description']??''))
        ]);

        update_post_meta($id,'_wp_attachment_image_alt',sanitize_text_field((string)($m['alt']??'')));
        foreach([
            'hc_drive_file_id'=>'drive_file_id',
            'hc_drive_url'=>'drive_url',
            'hc_ai_summary'=>'ai_summary',
            'hc_ai_alt'=>'ai_alt',
            'hc_ai_caption'=>'ai_caption',
            'hc_ai_visual_type'=>'ai_visual_type'
        ] as $meta=>$key){
            if(!array_key_exists($key,$m)) continue;
            $v=(string)$m[$key];
            update_post_meta($id,$meta,$meta==='hc_drive_url'?esc_url_raw($v):sanitize_textarea_field($v));
        }
        update_post_meta($id,'hc_ai_tags',array_values(array_filter(array_map('sanitize_text_field',(array)($m['ai_tags']??[])))));
        update_post_meta($id,'hc_ai_cover_score',(int)($m['cover_score']??0));
        $updated++;
    }
    return $updated;
}

function hcdecor_restore_settings($backup){
    $s=(array)($backup['settings']??[]);

    $primary=sanitize_key((string)($s['ai_primary']??'auto'));
    if(in_array($primary,['auto','openai','gemini'],true)) update_option('hcdecor_ai_primary',$primary,false);

    if(isset($s['openai_model'])) update_option('hcdecor_ai_openai_model',sanitize_text_field((string)$s['openai_model']),false);
    if(isset($s['gemini_model'])) update_option('hcdecor_ai_gemini_model',sanitize_text_field((string)$s['gemini_model']),false);

    if(isset($s['active_prompt'])) update_option('hcdecor_drive_active_prompt',wp_kses_post((string)$s['active_prompt']),false);
    if(isset($s['active_prompt_title'])) update_option('hcdecor_drive_active_prompt_title',sanitize_text_field((string)$s['active_prompt_title']),false);
    if(isset($s['active_prompt_file'])) update_option('hcdecor_drive_active_prompt_file',sanitize_text_field((string)$s['active_prompt_file']),false);

    if(!empty($s['automation']) && is_array($s['automation'])){
        $current=function_exists('hcdecor_auto_settings')?hcdecor_auto_settings():[];
        foreach(['enabled','social_enabled','evergreen_enabled'] as $k){
            if(array_key_exists($k,$s['automation'])) $current[$k]=(bool)$s['automation'][$k];
        }
        foreach(['evergreen_days','max_attempts','retry_minutes'] as $k){
            if(array_key_exists($k,$s['automation'])) $current[$k]=(int)$s['automation'][$k];
        }
        // Preserve current webhook URL/credentials. Backup intentionally excludes secrets.
        update_option('hcdecor_automation_settings',$current,false);
    }

    if(!empty($s['recipes']) && is_array($s['recipes'])){
        update_option('hcdecor_automation_recipes',$s['recipes'],false);
    }
    return true;
}

function hcdecor_restore_apply($file_id,$sections){
    $backup=hcdecor_restore_read_backup($file_id);
    if(is_wp_error($backup)) return $backup;

    // Disaster-recovery guardrail: snapshot current state first.
    if(function_exists('hcdecor_backup_save')){
        $safety=hcdecor_backup_save();
        if(is_wp_error($safety)) return new WP_Error('safety_backup','Không tạo được safety backup: '.$safety->get_error_message());
    }

    $sections=array_values(array_intersect(['projects','jobs','media','settings'],(array)$sections));
    $media_map=hcdecor_restore_media_map($backup);
    $project_result=['map'=>[],'created'=>0,'updated'=>0];
    $job_result=['created'=>0,'updated'=>0];
    $media_updated=0;

    if(in_array('projects',$sections,true)){
        $project_result=hcdecor_restore_projects($backup,$media_map);
    }else{
        foreach((array)($backup['projects']??[]) as $p){
            $old=(int)($p['id']??0);
            if($old && get_post_type($old)==='hc_project') $project_result['map'][$old]=$old;
        }
    }

    if(in_array('jobs',$sections,true)){
        $job_result=hcdecor_restore_jobs($backup,$project_result['map'],$media_map);
    }
    if(in_array('media',$sections,true)){
        $media_updated=hcdecor_restore_media_metadata($backup,$media_map);
    }
    if(in_array('settings',$sections,true)){
        hcdecor_restore_settings($backup);
    }

    $result=[
        'restored_at'=>current_time('mysql'),
        'source_file_id'=>sanitize_text_field($file_id),
        'sections'=>$sections,
        'projects'=>['created'=>$project_result['created'],'updated'=>$project_result['updated']],
        'jobs'=>$job_result,
        'media_updated'=>$media_updated,
        'media_missing'=>count(array_filter($media_map,function($x){return !$x;}))
    ];
    update_option('hcdecor_restore_last_result',$result,false);
    return $result;
}

add_action('admin_post_hcdecor_restore_apply',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_restore_apply');

    $file=sanitize_text_field(wp_unslash($_POST['file_id']??''));
    $confirm=strtoupper(trim((string)wp_unslash($_POST['confirm']??'')));
    if($confirm!=='RESTORE') wp_die('Nhập RESTORE để xác nhận.');

    $sections=(array)($_POST['sections']??[]);
    $r=hcdecor_restore_apply($file,$sections);
    if(is_wp_error($r)){
        update_option('hcdecor_restore_last_error',$r->get_error_message(),false);
        wp_safe_redirect(admin_url('admin.php?page=hcdecor-restore-center&file_id='.rawurlencode($file).'&failed=1')); exit;
    }
    delete_option('hcdecor_restore_last_error');
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-restore-center&file_id='.rawurlencode($file).'&done=1')); exit;
});

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','Restore Center','Restore Center','manage_options','hcdecor-restore-center','hcdecor_restore_page',9);
},32);

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/restore/plan',[
        'methods'=>'GET',
        'permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $backup=hcdecor_restore_read_backup((string)$r->get_param('file_id'));
            return is_wp_error($backup)?$backup:rest_ensure_response(hcdecor_restore_plan($backup));
        }
    ]);
});

function hcdecor_restore_page(){
    if(!current_user_can('manage_options')) return;
    $file=sanitize_text_field(wp_unslash($_GET['file_id']??''));
    $backup=$file?hcdecor_restore_read_backup($file):null;
    $plan=is_array($backup)?hcdecor_restore_plan($backup):null;
    $files=[];
    if(function_exists('hcdecor_drive_configured') && hcdecor_drive_configured() && function_exists('hcdecor_drive_list')){
        $files=hcdecor_drive_list(function_exists('hcdecor_backup_folder_id')?hcdecor_backup_folder_id():'',30);
        if(is_wp_error($files)) $files=[];
    }
    $last=(array)get_option('hcdecor_restore_last_result',[]);
    $error=(string)get_option('hcdecor_restore_last_error','');
    ?>
    <div class="wrap hcrs" style="max-width:1250px">
      <style>
      .hcrs-grid{display:grid;grid-template-columns:390px 1fr;gap:14px}.hcrs-card{background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px}
      .hcrs-file{display:block;border-top:1px solid #eee;padding:10px 0}.hcrs-file:first-child{border-top:0}.hcrs-kpi{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:12px 0}.hcrs-kpi>div{background:#f6f7f7;border-radius:10px;padding:10px}
      .hcrs-warn{background:#fff8e5;border:1px solid #dba617;border-radius:10px;padding:11px}.hcrs-actions{display:flex;gap:8px;flex-wrap:wrap}
      @media(max-width:800px){.hcrs-grid{grid-template-columns:1fr}.hcrs-kpi{grid-template-columns:1fr}.hcrs .button{min-height:42px}}
      </style>
      <h1>HCDecor Restore Center</h1>
      <p>Khôi phục có kiểm soát từ Drive Backup. Hệ thống luôn tạo một safety backup mới trước khi restore.</p>

      <?php if(isset($_GET['done'])):?><div class="notice notice-success inline"><p>Restore hoàn tất.</p></div><?php endif;?>
      <?php if(isset($_GET['failed'])):?><div class="notice notice-error inline"><p>Restore thất bại. <?php echo esc_html($error);?></p></div><?php endif;?>

      <div class="hcrs-grid">
        <section class="hcrs-card">
          <h2>Drive Backups</h2>
          <?php if(!$files):?><p>Chưa có backup hoặc Drive chưa kết nối.</p><?php endif;?>
          <?php foreach($files as $x):?>
            <div class="hcrs-file">
              <strong><?php echo esc_html($x['name']??'Backup');?></strong><br>
              <small><?php echo esc_html($x['modifiedTime']??'');?></small><br>
              <a class="button button-small" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-restore-center&file_id='.rawurlencode((string)($x['id']??''))));?>">Preview Restore</a>
            </div>
          <?php endforeach;?>
        </section>

        <section class="hcrs-card">
          <h2>Restore Preview</h2>
          <?php if($backup instanceof WP_Error):?>
            <p><?php echo esc_html($backup->get_error_message());?></p>
          <?php elseif(!$plan):?>
            <p>Chọn một backup bên trái để xem kế hoạch restore.</p>
          <?php else:?>
            <p><strong>Backup:</strong> <?php echo esc_html($plan['backup_created_at']);?> · Sync <?php echo esc_html($plan['sync_version']?:'—');?></p>
            <p><strong>Source:</strong> <?php echo esc_html($plan['backup_site']);?></p>

            <div class="hcrs-kpi">
              <div><strong>Projects</strong><br>Update <?php echo (int)$plan['projects']['update'];?> · Create <?php echo (int)$plan['projects']['create'];?></div>
              <div><strong>Content Jobs</strong><br>Update <?php echo (int)$plan['jobs']['update'];?> · Create <?php echo (int)$plan['jobs']['create'];?></div>
              <div><strong>Media</strong><br>Matched <?php echo (int)$plan['media']['matched'];?> · Missing <?php echo (int)$plan['media']['missing'];?></div>
            </div>

            <div class="hcrs-warn">
              <strong>Safe Restore</strong><br>
              Media file thiếu sẽ không bị tạo giả. Chỉ metadata của media đã match bằng WordPress ID hoặc Drive File ID mới được phục hồi. API key/OAuth secret không bị thay đổi.
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" style="margin-top:14px">
              <input type="hidden" name="action" value="hcdecor_restore_apply">
              <input type="hidden" name="file_id" value="<?php echo esc_attr($file);?>">
              <?php wp_nonce_field('hcdecor_restore_apply');?>

              <p><strong>Restore sections</strong></p>
              <?php foreach(['projects'=>'Projects','jobs'=>'Content Jobs','media'=>'Media Metadata','settings'=>'Non-secret Settings'] as $k=>$label):?>
                <label style="display:block;margin:7px 0"><input type="checkbox" name="sections[]" value="<?php echo esc_attr($k);?>" checked> <?php echo esc_html($label);?></label>
              <?php endforeach;?>

              <p><label><strong>Xác nhận</strong><br><input type="text" name="confirm" autocomplete="off" placeholder="Nhập RESTORE" required></label></p>
              <div class="hcrs-actions">
                <button class="button button-primary">Safety Backup + Restore</button>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-data-backups'));?>">Data Backups</a>
              </div>
            </form>
          <?php endif;?>

          <?php if($last):?>
            <hr><h3>Last Restore</h3>
            <p><?php echo esc_html($last['restored_at']??'');?> · Projects <?php echo (int)(($last['projects']['created']??0)+($last['projects']['updated']??0));?> · Jobs <?php echo (int)(($last['jobs']['created']??0)+($last['jobs']['updated']??0));?> · Media <?php echo (int)($last['media_updated']??0);?></p>
          <?php endif;?>
        </section>
      </div>
    </div><?php
}
