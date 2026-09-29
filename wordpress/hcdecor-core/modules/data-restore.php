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
    if(!function_exists('hcdecor_drive_download') || !function_exists('hcdecor_drive_file_meta') || !function_exists('hcdecor_drive_file_in_managed_folders')) return new WP_Error('drive','Drive Vault unavailable.');
    $meta=hcdecor_drive_file_meta($file_id);
    if(is_wp_error($meta)) return $meta;
    if(!hcdecor_drive_file_in_managed_folders($meta,['backups'])) return new WP_Error('scope','Restore source is outside the managed BACKUPS folder.');
    $mime=strtolower(hcdecor_restore_clip($meta['mimeType']??'',100));
    if(!in_array($mime,['application/json','text/json','text/plain'],true)) return new WP_Error('mime','Restore source must be a JSON backup file.');
    if((int)($meta['size']??0)<=0 || (int)($meta['size']??0)>25*1024*1024) return new WP_Error('size','Backup file must be between 1 byte and 25 MB.');

    $body=hcdecor_drive_download($file_id,25*1024*1024);
    if(is_wp_error($body)) return $body;
    if(strlen((string)$body)>25*1024*1024) return new WP_Error('size','Backup vượt giới hạn 25MB.');

    $data=json_decode((string)$body,true);
    if(!is_array($data) || ($data['schema']??'')!=='hcdecor.system-backup.v1'){
        return new WP_Error('schema','Không phải HCDecor system backup hợp lệ.');
    }
    $integrity=(array)($data['integrity']??[]);
    if(($integrity['algorithm']??'')!=='sha256' || empty($integrity['payload_hash'])) return new WP_Error('integrity','Backup integrity metadata is required.');
    $expected=sanitize_text_field((string)$integrity['payload_hash']);
    if(!preg_match('/^[a-f0-9]{64}$/i',$expected)) return new WP_Error('integrity','Backup integrity hash is invalid.');
    $check=$data; unset($check['integrity']);
    $payload=wp_json_encode($check,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    if(!is_string($payload) || !hash_equals(strtolower($expected),hash('sha256',$payload))){
        return new WP_Error('integrity','Backup integrity check failed.');
    }
    $limits=['projects'=>2000,'content_jobs'=>5000,'media_index'=>10000];
    foreach($limits as $section=>$limit){
        if(isset($data[$section]) && (!is_array($data[$section]) || count($data[$section])>$limit)){
            return new WP_Error('restore_limit','Backup '.$section.' exceeds the safe restore object limit.');
        }
    }
    return $data;
}

function hcdecor_restore_clip($value,$limit,$html=false){
    $value=is_scalar($value)?(string)$value:'';$text=$html?wp_kses_post($value):sanitize_textarea_field($value);
    return function_exists('mb_substr')?mb_substr($text,0,$limit):substr($text,0,$limit);
}

function hcdecor_restore_media_map($backup){
    $map=[];
    foreach((array)($backup['media_index']??[]) as $m){
        $old=(int)($m['id']??0);
        if(!$old) continue;

        $drive_id=sanitize_text_field(hcdecor_restore_clip($m['drive_file_id']??'',300));
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

function hcdecor_restore_existing_by_identity($type,$row){
    $identity=sanitize_text_field(hcdecor_restore_clip($row['backup_identity']??'',100));
    if($identity==='') return 0;
    $found=get_posts([
        'post_type'=>$type,'post_status'=>'any','numberposts'=>2,'fields'=>'ids',
        'meta_key'=>'hc_backup_identity','meta_value'=>$identity
    ]);
    return count($found)===1?(int)$found[0]:0;
}

function hcdecor_restore_plan($backup){
    $media_map=hcdecor_restore_media_map($backup);
    $projects=['update'=>0,'create'=>0];
    $jobs=['update'=>0,'create'=>0];
    $media=['matched'=>0,'missing'=>0];

    foreach((array)($backup['projects']??[]) as $p){
        if(hcdecor_restore_existing_by_identity('hc_project',$p)) $projects['update']++;
        else $projects['create']++;
    }

    foreach((array)($backup['content_jobs']??[]) as $j){
        if(hcdecor_restore_existing_by_identity('hc_content_job',$j)) $jobs['update']++;
        else $jobs['create']++;
    }

    foreach($media_map as $id=>$mapped){
        if($mapped) $media['matched']++; else $media['missing']++;
    }

    return [
        'backup_created_at'=>hcdecor_restore_clip($backup['created_at']??'',64),
        'backup_site'=>hcdecor_restore_clip($backup['site']['url']??'',500),
        'sync_version'=>hcdecor_restore_clip($backup['site']['sync_version']??'',100),
        'projects'=>$projects,
        'jobs'=>$jobs,
        'media'=>$media,
        'settings'=>!empty($backup['settings'])
    ];
}

function hcdecor_restore_map_ids($ids,$map,$limit=60){
    $out=[];
    $limit=max(1,min(60,(int)$limit));
    foreach(array_slice((array)$ids,0,$limit) as $id){
        $old=(int)$id;
        $new=(int)($map[$old]??0);
        if($new) $out[]=$new;
        if(count($out)>=$limit) break;
    }
    return array_values(array_unique($out));
}

function hcdecor_restore_projects($backup,$media_map){
    $project_map=[];
    $created=0; $updated=0;

    foreach((array)($backup['projects']??[]) as $p){
        $old=(int)($p['id']??0);
        $existing=hcdecor_restore_existing_by_identity('hc_project',$p);
        $status=in_array(($p['status']??''),['publish','draft','private'],true)?$p['status']:'draft';

        $post=[
            'post_type'=>'hc_project',
            'post_status'=>$status,
            'post_title'=>hcdecor_restore_clip($p['title']??'Restored Project',300),
            'post_excerpt'=>hcdecor_restore_clip($p['excerpt']??'',5000),
            'post_content'=>hcdecor_restore_clip($p['content']??'',100000,true)
        ];
        if($existing) $post['ID']=$existing;

        $r=$existing?wp_update_post($post,true):wp_insert_post($post,true);
        if(is_wp_error($r)) continue;
        $id=(int)$r;
        $identity=sanitize_text_field(hcdecor_restore_clip($p['backup_identity']??'',100));
        if($identity!=='') update_post_meta($id,'hc_backup_identity',$identity);
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
            if(array_key_exists($key,$p)) update_post_meta($id,$meta,hcdecor_restore_clip($p[$key],$key==='summary'?10000:2000));
        }

        $thumb_old=(int)($p['thumbnail_id']??0);
        $thumb=(int)($media_map[$thumb_old]??0);
        if($thumb && wp_attachment_is_image($thumb)) set_post_thumbnail($id,$thumb);
        else delete_post_thumbnail($id);
    }

    return ['map'=>$project_map,'created'=>$created,'updated'=>$updated];
}

function hcdecor_restore_jobs($backup,$project_map,$media_map){
    $created=0; $updated=0;
    $allowed_status=['draft','review','failed'];

    foreach((array)($backup['content_jobs']??[]) as $j){
        $old=(int)($j['id']??0);
        $existing=hcdecor_restore_existing_by_identity('hc_content_job',$j);

        $post=[
            'post_type'=>'hc_content_job',
            'post_status'=>'publish',
            'post_title'=>hcdecor_restore_clip($j['title']??'Restored Content Job',300),
            'post_content'=>hcdecor_restore_clip($j['brief']??'',20000)
        ];
        if($existing) $post['ID']=$existing;

        $r=$existing?wp_update_post($post,true):wp_insert_post($post,true);
        if(is_wp_error($r)) continue;
        $id=(int)$r;
        $identity=sanitize_text_field(hcdecor_restore_clip($j['backup_identity']??'',100));
        if($identity!=='') update_post_meta($id,'hc_backup_identity',$identity);
        $existing?$updated++:$created++;

        $old_project=(int)($j['project_id']??0);
        $project=(int)($project_map[$old_project]??0);
        update_post_meta($id,'hc_project_id',$project);

        $status=sanitize_key(hcdecor_restore_clip($j['status']??'draft',50));
        if(in_array($status,['approved','published_web'],true)) $status='review';
        elseif($status==='processing') $status='draft';
        update_post_meta($id,'hc_agent_status',in_array($status,$allowed_status,true)?$status:'draft');
        delete_post_meta($id,'hc_agent_claim_token');
        delete_post_meta($id,'hc_agent_lock_until');
        delete_post_meta($id,'hc_publish_approved_by');
        delete_post_meta($id,'hc_publish_approved_at');
        delete_post_meta($id,'hc_publish_approval_source');
        delete_post_meta($id,'hc_publish_snapshot');
        delete_post_meta($id,'hc_published_project_id');
        delete_post_meta($id,'hc_published_web_at');
        delete_post_meta($id,'hc_published_web_by');
        delete_post_meta($id,'hc_published_web_url');

        $channels=array_values(array_intersect(['web','facebook','tiktok','youtube'],(array)($j['channels']??[])));
        update_post_meta($id,'hc_channels',$channels);

        $media=hcdecor_restore_map_ids((array)($j['media_ids']??[]),$media_map);
        update_post_meta($id,'hc_media_ids',$media);

        $cover_old=(int)($j['cover_id']??0);
        $cover=(int)($media_map[$cover_old]??0);
        if(!$cover || !in_array($cover,$media,true) || !wp_attachment_is_image($cover)) $cover=0;
        update_post_meta($id,'hc_cover_id',$cover);

        if(function_exists('hcdecor_ops_save_fields')){
            hcdecor_ops_save_fields($id,(array)($j['content']??[]));
        }

        update_post_meta($id,'hc_ai_provider',sanitize_key(hcdecor_restore_clip($j['ai_provider']??'',50)));
        update_post_meta($id,'hc_ai_model',hcdecor_restore_clip($j['ai_model']??'',200));

        if(!empty($j['workflow_log']) && is_array($j['workflow_log'])){
            $workflow_log=[];
            foreach(array_slice($j['workflow_log'],-100) as $entry){
                if(!is_array($entry)) continue;
                $workflow_log[]=[
                    'time'=>hcdecor_restore_clip($entry['time']??'',64),
                    'event'=>sanitize_key(hcdecor_restore_clip($entry['event']??'',100)),
                    'note'=>hcdecor_restore_clip($entry['note']??'',500),
                    'user'=>(int)($entry['user']??0)
                ];
            }
            update_post_meta($id,'hc_workflow_log',$workflow_log);
        }else{
            delete_post_meta($id,'hc_workflow_log');
        }

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
            'post_title'=>hcdecor_restore_clip($m['title']??'',300),
            'post_excerpt'=>hcdecor_restore_clip($m['caption']??'',5000),
            'post_content'=>hcdecor_restore_clip($m['description']??'',10000)
        ]);

        update_post_meta($id,'_wp_attachment_image_alt',hcdecor_restore_clip($m['alt']??'',1000));
        foreach([
            'hc_ai_summary'=>'ai_summary',
            'hc_ai_alt'=>'ai_alt',
            'hc_ai_caption'=>'ai_caption',
            'hc_ai_visual_type'=>'ai_visual_type'
        ] as $meta=>$key){
            if(!array_key_exists($key,$m)) continue;
            $v=(string)$m[$key];
            $limit=$meta==='hc_ai_summary'?5000:($meta==='hc_ai_caption'?5000:1000);
            update_post_meta($id,$meta,hcdecor_restore_clip($v,$limit));
        }
        $tags=[];
        foreach(array_slice((array)($m['ai_tags']??[]),0,30) as $tag){
            $tag=hcdecor_restore_clip($tag,100);
            if($tag!=='') $tags[]=$tag;
        }
        update_post_meta($id,'hc_ai_tags',array_values(array_unique($tags)));
        update_post_meta($id,'hc_ai_cover_score',(int)($m['cover_score']??0));
        $updated++;
    }
    return $updated;
}

function hcdecor_restore_settings($backup){
    $s=(array)($backup['settings']??[]);

    $primary=sanitize_key(hcdecor_restore_clip($s['ai_primary']??'auto',50));
    if(in_array($primary,['auto','openai','gemini'],true)) update_option('hcdecor_ai_primary',$primary,false);

    if(isset($s['openai_model'])) update_option('hcdecor_ai_openai_model',hcdecor_restore_clip($s['openai_model'],200),false);
    if(isset($s['gemini_model'])) update_option('hcdecor_ai_gemini_model',hcdecor_restore_clip($s['gemini_model'],200),false);

    if(isset($s['active_prompt'])) update_option('hcdecor_drive_active_prompt',hcdecor_restore_clip($s['active_prompt'],20000,true),false);
    if(isset($s['active_prompt_title'])) update_option('hcdecor_drive_active_prompt_title',hcdecor_restore_clip($s['active_prompt_title'],300),false);
    if(isset($s['active_prompt_file'])) update_option('hcdecor_drive_active_prompt_file',hcdecor_restore_clip($s['active_prompt_file'],300),false);

    if(!empty($s['automation']) && is_array($s['automation'])){
        $current=function_exists('hcdecor_auto_settings')?hcdecor_auto_settings():[];
        // Disaster recovery restores configuration values but never re-enables background or outbound automation.
        $current['enabled']=false;
        $current['social_enabled']=false;
        $current['webhook_enabled']=false;
        $current['evergreen_enabled']=false;
        if(array_key_exists('evergreen_days',$s['automation'])) $current['evergreen_days']=max(7,min(3650,(int)$s['automation']['evergreen_days']));
        if(array_key_exists('max_attempts',$s['automation'])) $current['max_attempts']=max(1,min(10,(int)$s['automation']['max_attempts']));
        if(array_key_exists('retry_minutes',$s['automation'])) $current['retry_minutes']=max(1,min(1440,(int)$s['automation']['retry_minutes']));
        // Preserve current webhook URL/credentials. Backup intentionally excludes secrets.
        update_option('hcdecor_automation_settings',$current,false);
    }

    if(!empty($s['recipes']) && is_array($s['recipes']) && function_exists('hcdecor_recipe_defaults')){
        // Restore only recipes defined by the current code version; never restore executable recipe structures raw.
        $saved_by_id=[];
        foreach($s['recipes'] as $recipe){
            if(!is_array($recipe)) continue;
            $rid=sanitize_key(hcdecor_restore_clip($recipe['id']??'',100));
            if($rid!=='') $saved_by_id[$rid]=$recipe;
        }
        $recipes=[];
        foreach(hcdecor_recipe_defaults() as $default){
            $rid=sanitize_key(hcdecor_restore_clip($default['id']??'',100));
            if($rid==='' || !isset($saved_by_id[$rid])) continue;
            $default['enabled']=false;
            $recipes[]=$default;
        }
        if(!$recipes){
            foreach(hcdecor_recipe_defaults() as $default){
                if(!is_array($default)) continue;
                $default['enabled']=false;
                $recipes[]=$default;
            }
        }
        update_option('hcdecor_automation_recipes',$recipes,false);
    }
    return true;
}

function hcdecor_restore_apply($file_id,$sections){
    $backup=hcdecor_restore_read_backup($file_id);
    if(is_wp_error($backup)) return $backup;

    // Disaster-recovery guardrail: snapshot current state first.
    if(function_exists('hcdecor_backup_save')){
        $safety=hcdecor_backup_save();
        if(is_wp_error($safety)) return new WP_Error('safety_backup','Không tạo được safety backup: '.(function_exists('hcdecor_drive_safe_error')?hcdecor_drive_safe_error($safety->get_error_message()):'backup failed'));
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
            $existing=hcdecor_restore_existing_by_identity('hc_project',$p);
            if($old && $existing) $project_result['map'][$old]=$existing;
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

    $file_raw=wp_unslash($_POST['file_id']??'');$file=sanitize_text_field(is_scalar($file_raw)?(string)$file_raw:'');
    $confirm_raw=wp_unslash($_POST['confirm']??'');$confirm=strtoupper(is_scalar($confirm_raw)?trim((string)$confirm_raw):'');
    if($confirm!=='RESTORE') wp_die('Nhập RESTORE để xác nhận.');

    $sections=(array)($_POST['sections']??[]);
    if(in_array('projects',$sections,true)){
        $backup=hcdecor_restore_read_backup($file);
        if(is_wp_error($backup)) wp_die(esc_html(function_exists('hcdecor_drive_safe_error')?hcdecor_drive_safe_error($backup->get_error_message()):'Backup validation failed.'));
        $restores_public=false;
        foreach((array)($backup['projects']??[]) as $project){
            if(($project['status']??'')==='publish'){ $restores_public=true; break; }
        }
        if($restores_public && (!is_scalar($_POST['approve_public_projects']??'') || (string)$_POST['approve_public_projects']!=='1')){
            wp_die('Explicit approval is required to restore public projects.');
        }
    }
    $r=hcdecor_restore_apply($file,$sections);
    if(is_wp_error($r)){
        update_option('hcdecor_restore_last_error',function_exists('hcdecor_drive_safe_error')?hcdecor_drive_safe_error($r->get_error_message()):'Restore failed.',false);
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
            $file=preg_replace('/[^A-Za-z0-9_-]/','',(string)$r->get_param('file_id'));
            if($file==='') return new WP_Error('file','Invalid Drive file ID.',['status'=>400]);
            $backup=hcdecor_restore_read_backup($file);
            return is_wp_error($backup)?$backup:rest_ensure_response(hcdecor_restore_plan($backup));
        }
    ]);
});

function hcdecor_restore_page(){
    if(!current_user_can('manage_options')) return;
    $file_raw=wp_unslash($_GET['file_id']??'');$file=preg_replace('/[^A-Za-z0-9_-]/','',is_scalar($file_raw)?(string)$file_raw:'');
    $backup=$file?hcdecor_restore_read_backup($file):null;
    $plan=is_array($backup)?hcdecor_restore_plan($backup):null;
    $files=[];
    if(function_exists('hcdecor_drive_configured') && hcdecor_drive_configured() && function_exists('hcdecor_drive_list')){
        $files=hcdecor_drive_list(function_exists('hcdecor_backup_folder_id')?hcdecor_backup_folder_id():'',30);
        if(is_wp_error($files)) $files=[];
    }
    $last=(array)get_option('hcdecor_restore_last_result',[]);
    $error=hcdecor_restore_clip(get_option('hcdecor_restore_last_error',''),500);
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
              <a class="button button-small" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-restore-center&file_id='.rawurlencode(hcdecor_restore_clip($x['id']??'',300))));?>">Preview Restore</a>
            </div>
          <?php endforeach;?>
        </section>

        <section class="hcrs-card">
          <h2>Restore Preview</h2>
          <?php if($backup instanceof WP_Error):?>
            <p><?php echo esc_html(function_exists('hcdecor_drive_safe_error')?hcdecor_drive_safe_error($backup->get_error_message()):'Backup validation failed.');?></p>
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

              <?php
              $restore_has_public_projects=false;
              foreach((array)($backup['projects']??[]) as $project){
                  if(($project['status']??'')==='publish'){ $restore_has_public_projects=true; break; }
              }
              ?>
              <?php if($restore_has_public_projects):?>
                <p><label><input type="checkbox" name="approve_public_projects" value="1" required> Tôi xác nhận backup này có project public và cho phép khôi phục trạng thái public.</label></p>
              <?php endif;?>
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
