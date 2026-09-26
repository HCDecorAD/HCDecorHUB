<?php
if (!defined('ABSPATH')) exit;

/* HCDecor production media manager: upload/select, metadata visibility, project assignment. */

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','Media Manager','Media Manager','upload_files','hcdecor-media','hcdecor_media_manager_page',3);
},25);

add_action('admin_enqueue_scripts',function($hook){
    if(strpos((string)$hook,'hcdecor-media')===false) return;
    wp_enqueue_media();
});

add_action('admin_post_hcdecor_media_assign',function(){
    if(!current_user_can('upload_files')) wp_die('Forbidden');
    check_admin_referer('hcdecor_media_assign');
    $project=(int)($_POST['project_id']??0);
    $ids=array_values(array_unique(array_filter(array_map('intval',(array)($_POST['media_ids']??[])))));
    if(!$project || get_post_type($project)!=='hc_project') wp_die('Invalid project');
    $existing=(array)get_post_meta($project,'hc_project_gallery',true);
    $merged=array_values(array_unique(array_merge(array_map('intval',$existing),$ids)));
    update_post_meta($project,'hc_project_gallery',$merged);
    update_post_meta($project,'hc_gallery_ids',$merged);
    if(!has_post_thumbnail($project) && !empty($ids[0]) && wp_attachment_is_image($ids[0])) set_post_thumbnail($project,$ids[0]);
    $next=sanitize_key($_POST['next_action']??'assign');
    if($next==='agent' && function_exists('hcdecor_agent_create_job')){
        $brief=sanitize_textarea_field(wp_unslash($_POST['agent_brief']??''));
        $job=hcdecor_agent_create_job($project,$ids,$brief);
        if(!is_wp_error($job)){
            wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$job.'&created=1')); exit;
        }
    }
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-media&assigned='.count($ids).'&project='.$project)); exit;
});

add_action('admin_post_hcdecor_media_meta',function(){
    if(!current_user_can('upload_files')) wp_die('Forbidden');
    $id=(int)($_POST['attachment_id']??0);
    check_admin_referer('hcdecor_media_meta_'.$id);
    if(get_post_type($id)!=='attachment') wp_die('Invalid media');
    wp_update_post([
        'ID'=>$id,
        'post_title'=>sanitize_text_field(wp_unslash($_POST['title']??'')),
        'post_excerpt'=>sanitize_textarea_field(wp_unslash($_POST['caption']??'')),
        'post_content'=>sanitize_textarea_field(wp_unslash($_POST['description']??''))
    ]);
    update_post_meta($id,'_wp_attachment_image_alt',sanitize_text_field(wp_unslash($_POST['alt']??'')));
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-media&media='.$id.'&updated=1')); exit;
});

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/media',[
        'methods'=>'GET',
        'permission_callback'=>function($r){
            if(function_exists('hcdecor_ops_bridge_auth')) return hcdecor_ops_bridge_auth();
            if(function_exists('hcdecor_bridge_auth')) return hcdecor_bridge_auth($r);
            return false;
        },
        'callback'=>function(WP_REST_Request $r){
            $limit=max(1,min(100,(int)($r->get_param('limit')?:50)));
            $items=get_posts(['post_type'=>'attachment','post_status'=>'inherit','post_mime_type'=>['image','video'],'numberposts'=>$limit,'orderby'=>'date','order'=>'DESC']);
            return rest_ensure_response(array_map(function($m){
                $meta=wp_get_attachment_metadata($m->ID);
                return [
                    'id'=>$m->ID,'title'=>$m->post_title,'caption'=>$m->post_excerpt,
                    'description'=>$m->post_content,'alt'=>(string)get_post_meta($m->ID,'_wp_attachment_image_alt',true),
                    'mime'=>$m->post_mime_type,'url'=>wp_get_attachment_url($m->ID),
                    'thumb'=>wp_get_attachment_image_url($m->ID,'medium')?:null,
                    'width'=>(int)($meta['width']??0),'height'=>(int)($meta['height']??0),
                    'date'=>$m->post_date_gmt
                ];
            },$items));
        }
    ]);
});

function hcdecor_media_manager_page(){
    if(!current_user_can('upload_files')) return;
    $projects=get_posts(['post_type'=>'hc_project','post_status'=>['publish','draft'],'numberposts'=>100,'orderby'=>'modified','order'=>'DESC']);
    $media=get_posts(['post_type'=>'attachment','post_status'=>'inherit','post_mime_type'=>['image','video'],'numberposts'=>60,'orderby'=>'date','order'=>'DESC']);
    $edit_id=(int)($_GET['media']??0); $edit=$edit_id&&get_post_type($edit_id)==='attachment'?get_post($edit_id):null;
    ?>
    <div class="wrap hcm"><style>
    .hcm{max-width:1450px}.hcm-head{display:flex;justify-content:space-between;align-items:center;margin:16px 0}.hcm-grid{display:grid;grid-template-columns:minmax(520px,1fr) 360px;gap:14px}.hcm-card{background:#fff;border:1px solid #dcdcde;border-radius:14px;overflow:hidden}.hcm-card h2{font-size:14px;margin:0;padding:14px 16px;border-bottom:1px solid #eee}.hcm-body{padding:14px}.hcm-library{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px}.hcm-item{position:relative;border:2px solid transparent;border-radius:10px;overflow:hidden;background:#eef0f2;cursor:pointer}.hcm-item.selected{border-color:#c7863d}.hcm-item img,.hcm-video{display:block;width:100%;aspect-ratio:1;object-fit:cover}.hcm-video{display:grid;place-items:center;background:#1c232a;color:#fff;font-weight:800}.hcm-check{position:absolute;top:6px;right:6px;background:#fff;border-radius:50%;width:24px;height:24px;display:grid;place-items:center;box-shadow:0 1px 5px #0003}.hcm-item.selected .hcm-check{background:#c7863d;color:#fff}.hcm-item small{display:block;padding:6px 7px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;background:#fff}.hcm-actions{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0}.hcm select,.hcm input[type=text],.hcm textarea{width:100%}.hcm textarea{min-height:90px}.hcm-meta img{max-width:100%;height:auto;border-radius:10px}.hcm-notice{background:#ecf7ed;border-left:4px solid #46b450;padding:10px 12px;margin:10px 0}@media(max-width:1100px){.hcm-grid{grid-template-columns:1fr}.hcm-library{grid-template-columns:repeat(4,1fr)}}@media(max-width:782px){.hcm-library{grid-template-columns:repeat(3,1fr)}}
    </style>
    <div class="hcm-head"><div><h1>HCDecor Media Manager</h1><p>Upload → chọn media → gắn vào Project → Agent xử lý nội dung.</p></div><strong><?php echo count($media);?> media gần nhất</strong></div>
    <?php if(isset($_GET['assigned'])||isset($_GET['updated'])||isset($_GET['ai_ok'])):?><div class="hcm-notice"><?php echo isset($_GET['ai_ok'])?'AI analyzed: '.intval($_GET['ai_ok']).' · failed: '.intval($_GET['ai_fail']??0):'Đã cập nhật.';?></div><?php endif;?>
    <div class="hcm-grid"><section class="hcm-card"><h2>MEDIA LIBRARY</h2><div class="hcm-body">
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" id="hcmAssign">
        <input type="hidden" name="action" value="hcdecor_media_assign"><?php wp_nonce_field('hcdecor_media_assign');?>
        <div class="hcm-actions"><button type="button" class="button button-primary" id="hcmUpload">+ Upload / chọn Media</button><a class="button" href="<?php echo esc_url(admin_url('upload.php'));?>">WordPress Media Library</a><button type="submit" class="button" formaction="<?php echo esc_url(admin_url('admin-post.php'));?>" name="action" value="hcdecor_media_ai_analyze">AI Analyze Selected</button></div><?php wp_nonce_field('hcdecor_media_ai_analyze');?>
        <div class="hcm-library" id="hcmLibrary">
        <?php foreach($media as $m): $is_video=strpos($m->post_mime_type,'video/')===0; $thumb=wp_get_attachment_image_url($m->ID,'medium');?>
          <div class="hcm-item" data-id="<?php echo $m->ID;?>">
            <?php if($thumb):?><img src="<?php echo esc_url($thumb);?>" alt=""><?php else:?><div class="hcm-video">VIDEO</div><?php endif;?>
            <span class="hcm-check">✓</span><small><?php echo esc_html($m->post_title?:basename(get_attached_file($m->ID)));?></small><?php $score=(int)get_post_meta($m->ID,'hc_ai_cover_score',true); $type=(string)get_post_meta($m->ID,'hc_ai_visual_type',true); if($score||$type):?><small style="color:#a5651d">AI <?php echo $score?esc_html($score.'/100'):'';?> <?php echo esc_html($type);?></small><?php endif;?>
          </div>
        <?php endforeach;?>
        </div><div id="hcmHidden"></div>
        <label><strong>Project</strong></label>
        <select name="project_id" required><option value="">— Chọn Project —</option><?php foreach($projects as $p):?><option value="<?php echo $p->ID;?>"><?php echo esc_html($p->post_title);?></option><?php endforeach;?></select>
        <label><strong>Brief cho HUB Agent</strong></label>
        <textarea name="agent_brief" placeholder="Ví dụ: phân tích media, chọn điểm nổi bật, tạo Project Story và nội dung Web/Facebook/TikTok/YouTube."></textarea>
        <div class="hcm-actions"><button class="button" name="next_action" value="assign">Chỉ gắn vào Project</button><button class="button button-primary" name="next_action" value="agent">Gắn + Gửi HUB Agent</button><span id="hcmCount">0 media đã chọn</span></div>
      </form>
    </div></section>
    <aside class="hcm-card"><h2>METADATA</h2><div class="hcm-body hcm-meta">
      <?php if($edit): $preview=wp_get_attachment_image_url($edit_id,'large');?>
        <?php if($preview):?><img src="<?php echo esc_url($preview);?>" alt=""><?php endif;?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
          <input type="hidden" name="action" value="hcdecor_media_meta"><input type="hidden" name="attachment_id" value="<?php echo $edit_id;?>"><?php wp_nonce_field('hcdecor_media_meta_'.$edit_id);?>
          <label>Title</label><input type="text" name="title" value="<?php echo esc_attr($edit->post_title);?>">
          <label>Alt text</label><input type="text" name="alt" value="<?php echo esc_attr(get_post_meta($edit_id,'_wp_attachment_image_alt',true));?>">
          <label>Caption</label><textarea name="caption"><?php echo esc_textarea($edit->post_excerpt);?></textarea>
          <label>Description</label><textarea name="description"><?php echo esc_textarea($edit->post_content);?></textarea><?php $ais=(string)get_post_meta($edit_id,'hc_ai_summary',true); $aiscore=(int)get_post_meta($edit_id,'hc_ai_cover_score',true); $aitags=(array)get_post_meta($edit_id,'hc_ai_tags',true); if($ais):?><hr><p><strong>AI Summary</strong><br><?php echo esc_html($ais);?></p><p><strong>Cover score:</strong> <?php echo $aiscore;?>/100</p><p><strong>Tags:</strong> <?php echo esc_html(implode(', ',$aitags));?></p><?php endif;?>
          <p><button class="button button-primary">Lưu metadata</button></p>
        </form>
      <?php else:?><p>Chọn một media rồi mở <strong>Edit metadata</strong>.</p><div id="hcmMetaLink"></div><?php endif;?>
    </div></aside></div>
    <script>
    (function(){
      const lib=document.getElementById('hcmLibrary'), hidden=document.getElementById('hcmHidden'), count=document.getElementById('hcmCount');
      let selected=[];
      function draw(){hidden.innerHTML='';selected.forEach(id=>{const x=document.createElement('input');x.type='hidden';x.name='media_ids[]';x.value=id;hidden.appendChild(x)});count.textContent=selected.length+' media đã chọn';const meta=document.getElementById('hcmMetaLink');if(meta&&selected[0])meta.innerHTML='<p><a class="button" href="<?php echo esc_js(admin_url('admin.php?page=hcdecor-media&media='));?>'+selected[0]+'">Edit metadata media đầu tiên</a></p>';}
      lib.addEventListener('click',e=>{const item=e.target.closest('.hcm-item');if(!item)return;const id=parseInt(item.dataset.id);if(selected.includes(id)){selected=selected.filter(x=>x!==id);item.classList.remove('selected')}else{selected.push(id);item.classList.add('selected')}draw()});
      document.getElementById('hcmUpload').onclick=function(){const frame=wp.media({title:'HCDecor Media',button:{text:'Dùng media đã chọn'},multiple:true});frame.on('select',function(){const items=frame.state().get('selection').toJSON();selected=items.map(x=>x.id);draw();location.reload();});frame.open();};
    })();
    </script></div><?php
}
