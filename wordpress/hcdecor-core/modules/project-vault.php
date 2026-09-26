<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor Project Vault
 * Project metadata + media references <-> Google Drive / 01_PROJECTS.
 */

function hcdecor_project_vault_media_record($attachment_id){
    $attachment_id=(int)$attachment_id;
    if(!$attachment_id || get_post_type($attachment_id)!=='attachment') return null;
    return [
        'wp_id'=>$attachment_id,
        'drive_file_id'=>(string)get_post_meta($attachment_id,'hc_drive_file_id',true),
        'drive_url'=>(string)get_post_meta($attachment_id,'hc_drive_url',true),
        'title'=>get_the_title($attachment_id),
        'mime'=>(string)get_post_mime_type($attachment_id),
        'alt'=>(string)get_post_meta($attachment_id,'_wp_attachment_image_alt',true),
        'caption'=>(string)wp_get_attachment_caption($attachment_id),
        'ai_summary'=>(string)get_post_meta($attachment_id,'hc_ai_summary',true),
        'ai_tags'=>(array)get_post_meta($attachment_id,'hc_ai_tags',true),
        'cover_score'=>(int)get_post_meta($attachment_id,'hc_ai_cover_score',true)
    ];
}

function hcdecor_project_vault_data($project_id){
    $project_id=(int)$project_id;
    $p=get_post($project_id);
    if(!$p || $p->post_type!=='hc_project') return new WP_Error('project','Invalid Project.');

    $gallery=(array)get_post_meta($project_id,'hc_project_gallery',true);
    if(!$gallery) $gallery=(array)get_post_meta($project_id,'hc_gallery_ids',true);
    $gallery=array_values(array_unique(array_filter(array_map('intval',$gallery))));
    $media=[];
    foreach($gallery as $mid){
        $row=hcdecor_project_vault_media_record($mid);
        if($row) $media[]=$row;
    }

    $featured_id=(int)get_post_thumbnail_id($project_id);
    $featured=$featured_id?hcdecor_project_vault_media_record($featured_id):null;
    $terms=wp_get_post_terms($project_id,'hc_project_type',['fields'=>'slugs']);
    if(is_wp_error($terms)) $terms=[];

    return [
        'schema'=>'hcdecor.project.v1',
        'saved_at'=>current_time('mysql'),
        'source_project_id'=>$project_id,
        'project'=>[
            'title'=>$p->post_title,
            'slug'=>$p->post_name,
            'status'=>$p->post_status,
            'excerpt'=>$p->post_excerpt,
            'content'=>$p->post_content,
            'url'=>get_permalink($project_id),
            'types'=>array_values((array)$terms),
            'client'=>(string)get_post_meta($project_id,'hc_client',true),
            'location'=>(string)get_post_meta($project_id,'hc_location',true),
            'year'=>(string)get_post_meta($project_id,'hc_year',true),
            'summary'=>(string)get_post_meta($project_id,'hc_summary',true),
            'seo_meta'=>(string)get_post_meta($project_id,'hc_seo_meta',true)
        ],
        'featured'=>$featured,
        'media'=>$media
    ];
}

function hcdecor_project_vault_sync_media($project_id){
    $gallery=(array)get_post_meta($project_id,'hc_project_gallery',true);
    if(!$gallery) $gallery=(array)get_post_meta($project_id,'hc_gallery_ids',true);
    $gallery=array_slice(array_values(array_unique(array_filter(array_map('intval',$gallery)))),0,60);
    $featured=(int)get_post_thumbnail_id($project_id);
    if($featured && !in_array($featured,$gallery,true)) array_unshift($gallery,$featured);

    $ok=0; $failed=0;
    foreach($gallery as $mid){
        if(get_post_type($mid)!=='attachment') continue;
        if((string)get_post_meta($mid,'hc_drive_file_id',true)!==''){ $ok++; continue; }
        if(!function_exists('hcdecor_drive_upload_attachment')){ $failed++; continue; }
        $r=hcdecor_drive_upload_attachment($mid,'media_input');
        is_wp_error($r)?$failed++:$ok++;
    }
    return ['ok'=>$ok,'failed'=>$failed];
}

function hcdecor_project_vault_save($project_id,$sync_media=true){
    $project_id=(int)$project_id;
    if(get_post_type($project_id)!=='hc_project') return new WP_Error('project','Invalid Project.');
    if(!function_exists('hcdecor_drive_configured') || !hcdecor_drive_configured()) return new WP_Error('drive','Google Drive chưa kết nối.');
    if(!function_exists('hcdecor_drive_save_json')) return new WP_Error('drive','Drive Vault unavailable.');

    if($sync_media) hcdecor_project_vault_sync_media($project_id);
    $data=hcdecor_project_vault_data($project_id);
    if(is_wp_error($data)) return $data;

    $existing=(string)get_post_meta($project_id,'hc_drive_project_file_id',true);
    $name=sanitize_file_name('PROJECT-'.$project_id.'-'.($data['project']['slug']?:$data['project']['title']).'.json');
    $r=hcdecor_drive_save_json($name,$data,'projects',$existing);

    if(is_wp_error($r)){
        update_post_meta($project_id,'hc_drive_project_error',$r->get_error_message());
        return $r;
    }

    update_post_meta($project_id,'hc_drive_project_file_id',sanitize_text_field((string)($r['id']??'')));
    update_post_meta($project_id,'hc_drive_project_url',esc_url_raw((string)($r['webViewLink']??'')));
    update_post_meta($project_id,'hc_drive_project_synced_at',current_time('mysql'));
    delete_post_meta($project_id,'hc_drive_project_error');
    return $r;
}

function hcdecor_project_vault_find_media($drive_id,$import=true){
    $drive_id=sanitize_text_field((string)$drive_id);
    if($drive_id==='') return 0;
    $found=get_posts([
        'post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'fields'=>'ids',
        'meta_key'=>'hc_drive_file_id','meta_value'=>$drive_id
    ]);
    if($found) return (int)$found[0];
    if(!$import || !function_exists('hcdecor_drive_import_media')) return 0;
    $r=hcdecor_drive_import_media($drive_id);
    return is_wp_error($r)?0:(int)$r;
}

function hcdecor_project_vault_import($file_id){
    $file_id=preg_replace('/[^A-Za-z0-9_-]/','',(string)$file_id);
    if(!$file_id || !function_exists('hcdecor_drive_download')) return new WP_Error('file','Invalid Project Vault file.');
    $body=hcdecor_drive_download($file_id);
    if(is_wp_error($body)) return $body;
    $d=json_decode($body,true);
    if(!is_array($d)||($d['schema']??'')!=='hcdecor.project.v1') return new WP_Error('schema','Invalid HCDecor Project file.');

    $source=(int)($d['source_project_id']??0);
    $id=$source&&get_post_type($source)==='hc_project'?$source:0;
    $project=(array)($d['project']??[]);
    $status=in_array(($project['status']??''),['publish','draft','private'],true)?$project['status']:'draft';

    $post=[
        'post_type'=>'hc_project',
        'post_status'=>$status,
        'post_title'=>sanitize_text_field((string)($project['title']??'Imported Project')),
        'post_excerpt'=>sanitize_textarea_field((string)($project['excerpt']??'')),
        'post_content'=>wp_kses_post((string)($project['content']??''))
    ];
    if($id) $post['ID']=$id;
    $r=$id?wp_update_post($post,true):wp_insert_post($post,true);
    if(is_wp_error($r)) return $r;
    $id=(int)$r;

    foreach([
        'hc_client'=>'client','hc_location'=>'location','hc_year'=>'year',
        'hc_summary'=>'summary','hc_seo_meta'=>'seo_meta'
    ] as $meta=>$key){
        if(array_key_exists($key,$project)) update_post_meta($id,$meta,sanitize_textarea_field((string)$project[$key]));
    }

    $gallery=[];
    foreach(array_slice((array)($d['media']??[]),0,60) as $m){
        $mid=hcdecor_project_vault_find_media((string)($m['drive_file_id']??''),true);
        if($mid) $gallery[]=$mid;
    }
    $gallery=array_values(array_unique($gallery));
    update_post_meta($id,'hc_project_gallery',$gallery);
    update_post_meta($id,'hc_gallery_ids',$gallery);

    $featured=(array)($d['featured']??[]);
    $featured_id=hcdecor_project_vault_find_media((string)($featured['drive_file_id']??''),true);
    if($featured_id) set_post_thumbnail($id,$featured_id);
    elseif($gallery && !has_post_thumbnail($id)) set_post_thumbnail($id,$gallery[0]);

    if(taxonomy_exists('hc_project_type') && !empty($project['types'])){
        wp_set_object_terms($id,array_map('sanitize_title',(array)$project['types']),'hc_project_type',false);
    }

    update_post_meta($id,'hc_drive_project_file_id',$file_id);
    update_post_meta($id,'hc_drive_project_loaded_at',current_time('mysql'));
    return $id;
}

add_action('save_post_hc_project',function($post_id,$post,$update){
    if(wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
    if(!function_exists('hcdecor_drive_configured') || !hcdecor_drive_configured()) return;
    if(!wp_next_scheduled('hcdecor_project_vault_async_save',[$post_id])){
        wp_schedule_single_event(time()+30,'hcdecor_project_vault_async_save',[(int)$post_id]);
    }
},30,3);

add_action('hcdecor_project_data_changed',function($project_id){
    $project_id=(int)$project_id;
    if(!$project_id || get_post_type($project_id)!=='hc_project') return;
    if(!function_exists('hcdecor_drive_configured') || !hcdecor_drive_configured()) return;
    if(!wp_next_scheduled('hcdecor_project_vault_async_save',[$project_id])){
        wp_schedule_single_event(time()+20,'hcdecor_project_vault_async_save',[$project_id]);
    }
},10,1);

add_action('hcdecor_project_vault_async_save',function($project_id){
    hcdecor_project_vault_save((int)$project_id,true);
},10,1);

add_action('hcdecor_after_web_publish',function($job_id,$project_id){
    if(function_exists('hcdecor_drive_configured') && hcdecor_drive_configured()){
        hcdecor_project_vault_save((int)$project_id,false);
    }
},45,2);

function hcdecor_project_vault_sync_all($limit=100){
    $limit=max(1,min(500,(int)$limit));
    if(!function_exists('hcdecor_drive_configured') || !hcdecor_drive_configured()) return new WP_Error('drive','Google Drive chưa kết nối.');

    $projects=get_posts([
        'post_type'=>'hc_project','post_status'=>['publish','draft','private'],
        'numberposts'=>$limit,'orderby'=>'modified','order'=>'DESC','fields'=>'ids'
    ]);
    $result=['scanned'=>count($projects),'synced'=>0,'failed'=>0,'errors'=>[]];
    foreach($projects as $project_id){
        $r=hcdecor_project_vault_save((int)$project_id,true);
        if(is_wp_error($r)){
            $result['failed']++;
            if(count($result['errors'])<10) $result['errors'][]='#'.(int)$project_id.': '.$r->get_error_message();
        }else{
            $result['synced']++;
        }
    }
    update_option('hcdecor_project_vault_bulk_last_at',current_time('mysql'),false);
    update_option('hcdecor_project_vault_bulk_last_result',$result,false);
    return $result;
}

add_action('admin_post_hcdecor_project_vault_save',function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    $id=(int)($_POST['project_id']??0);
    check_admin_referer('hcdecor_project_vault_save_'.$id);
    $r=hcdecor_project_vault_save($id,true);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-project-vault&'.(is_wp_error($r)?'failed=1':'saved=1'))); exit;
});

add_action('admin_post_hcdecor_project_vault_sync_all',function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    check_admin_referer('hcdecor_project_vault_sync_all');
    $r=hcdecor_project_vault_sync_all(100);
    if(is_wp_error($r)){
        update_option('hcdecor_project_vault_last_error',$r->get_error_message(),false);
        wp_safe_redirect(admin_url('admin.php?page=hcdecor-project-vault&bulk_failed=1')); exit;
    }
    delete_option('hcdecor_project_vault_last_error');
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-project-vault&bulk_done=1')); exit;
});

add_action('admin_post_hcdecor_project_vault_import',function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    $file=sanitize_text_field(wp_unslash($_POST['file_id']??''));
    check_admin_referer('hcdecor_project_vault_import_'.$file);
    $r=hcdecor_project_vault_import($file);
    if(is_wp_error($r)){
        update_option('hcdecor_project_vault_last_error',$r->get_error_message(),false);
        wp_safe_redirect(admin_url('admin.php?page=hcdecor-project-vault&import_failed=1')); exit;
    }
    delete_option('hcdecor_project_vault_last_error');
    wp_safe_redirect(admin_url('post.php?post='.(int)$r.'&action=edit&drive_loaded=1')); exit;
});

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','Project Vault','Project Vault','edit_posts','hcdecor-project-vault','hcdecor_project_vault_page',4);
},26);

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/projects/(?P<id>\d+)/drive-save',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $res=hcdecor_project_vault_save((int)$r['id'],true);
            return is_wp_error($res)?$res:rest_ensure_response($res);
        }
    ]);
    register_rest_route('hcdecor/v1','/projects/drive-sync-all',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $res=hcdecor_project_vault_sync_all((int)($r->get_param('limit')?:100));
            return is_wp_error($res)?$res:rest_ensure_response($res);
        }
    ]);
    register_rest_route('hcdecor/v1','/projects/drive-import',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $res=hcdecor_project_vault_import((string)$r->get_param('file_id'));
            return is_wp_error($res)?$res:rest_ensure_response(['project_id'=>$res]);
        }
    ]);
});

function hcdecor_project_vault_page(){
    if(!current_user_can('edit_posts')) return;
    $projects=get_posts(['post_type'=>'hc_project','post_status'=>['publish','draft','private'],'numberposts'=>60,'orderby'=>'modified','order'=>'DESC']);
    $drive_files=[];
    if(function_exists('hcdecor_drive_configured') && hcdecor_drive_configured() && function_exists('hcdecor_drive_list')){
        $folders=hcdecor_drive_folders();
        $drive_files=hcdecor_drive_list((string)($folders['projects']??''),60);
        if(is_wp_error($drive_files)) $drive_files=[];
    }
    $error=(string)get_option('hcdecor_project_vault_last_error','');
    ?>
    <div class="wrap hcpv" style="max-width:1400px">
      <style>
      .hcpv-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.hcpv-card{background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px}
      .hcpv-row{display:flex;justify-content:space-between;gap:10px;align-items:center;padding:10px 0;border-top:1px solid #eee}.hcpv-row:first-child{border-top:0}
      .hcpv-actions{display:flex;gap:6px;flex-wrap:wrap}@media(max-width:800px){.hcpv-grid{grid-template-columns:1fr}.hcpv-row{display:block}.hcpv-actions{margin-top:8px}.hcpv .button{min-height:42px}}
      </style>
      <h1>HCDecor Project Vault</h1>
      <p>WordPress Project ↔ Google Drive <strong>01_PROJECTS</strong>. Project JSON giữ metadata và Drive File ID của media.</p>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin:12px 0">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
          <input type="hidden" name="action" value="hcdecor_project_vault_sync_all"><?php wp_nonce_field('hcdecor_project_vault_sync_all');?>
          <button class="button button-primary">Sync All Projects → Drive</button>
        </form>
        <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-drive-inbox'));?>">Drive Inbox</a>
      </div>

      <?php if(isset($_GET['saved'])):?><div class="notice notice-success inline"><p>Project đã lưu vào Drive Vault.</p></div><?php endif;?>
      <?php if(isset($_GET['bulk_done'])): $br=(array)get_option('hcdecor_project_vault_bulk_last_result',[]);?><div class="notice notice-success inline"><p>Bulk sync hoàn tất: <?php echo (int)($br['synced']??0);?> synced · <?php echo (int)($br['failed']??0);?> failed.</p></div><?php endif;?>
      <?php if(isset($_GET['failed'])||isset($_GET['import_failed'])||isset($_GET['bulk_failed'])):?><div class="notice notice-error inline"><p><?php echo esc_html($error?:'Project Vault operation failed.');?></p></div><?php endif;?>

      <div class="hcpv-grid">
        <section class="hcpv-card">
          <h2>WordPress Projects</h2>
          <?php foreach($projects as $p):
            $fid=(string)get_post_meta($p->ID,'hc_drive_project_file_id',true);
            $synced=(string)get_post_meta($p->ID,'hc_drive_project_synced_at',true);
          ?>
            <div class="hcpv-row">
              <div><strong>#<?php echo $p->ID;?> · <?php echo esc_html($p->post_title);?></strong><br><small><?php echo $fid?'Drive synced '.esc_html($synced):'Not saved to Drive';?></small></div>
              <div class="hcpv-actions">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
                  <input type="hidden" name="action" value="hcdecor_project_vault_save"><input type="hidden" name="project_id" value="<?php echo $p->ID;?>"><?php wp_nonce_field('hcdecor_project_vault_save_'.$p->ID);?>
                  <button class="button"><?php echo $fid?'Sync → Drive':'Save → Drive';?></button>
                </form>
                <a class="button" href="<?php echo esc_url(get_edit_post_link($p->ID));?>">Edit</a>
                <?php $u=(string)get_post_meta($p->ID,'hc_drive_project_url',true); if($u):?><a class="button" target="_blank" rel="noopener" href="<?php echo esc_url($u);?>">Drive</a><?php endif;?>
              </div>
            </div>
          <?php endforeach; if(!$projects):?><p>Chưa có Project.</p><?php endif;?>
        </section>

        <section class="hcpv-card">
          <h2>Drive Project Manifests</h2>
          <?php foreach($drive_files as $x):
            $name=(string)($x['name']??'');
            if(substr($name,-5)!=='.json') continue;
            $fid=(string)($x['id']??'');
          ?>
            <div class="hcpv-row">
              <div><strong><?php echo esc_html($name);?></strong><br><small><?php echo esc_html((string)($x['modifiedTime']??''));?></small></div>
              <div class="hcpv-actions">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
                  <input type="hidden" name="action" value="hcdecor_project_vault_import"><input type="hidden" name="file_id" value="<?php echo esc_attr($fid);?>"><?php wp_nonce_field('hcdecor_project_vault_import_'.$fid);?>
                  <button class="button button-primary">Load → WordPress</button>
                </form>
                <?php if(!empty($x['webViewLink'])):?><a class="button" target="_blank" rel="noopener" href="<?php echo esc_url($x['webViewLink']);?>">Open</a><?php endif;?>
              </div>
            </div>
          <?php endforeach; if(!$drive_files):?><p>Drive chưa kết nối hoặc 01_PROJECTS chưa có manifest.</p><?php endif;?>
        </section>
      </div>
    </div><?php
}
