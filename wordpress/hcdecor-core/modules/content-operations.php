<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor Content Operations
 * Real workflow: Project -> Media -> Content Job -> Review -> Web publish.
 * External/social publishing intentionally remains disabled.
 */

add_action('admin_menu', function () {
    add_submenu_page('hcdecor-hub','Content Operations','Content Operations','edit_posts','hcdecor-content-operations','hcdecor_ops_page',1);
}, 20);

add_action('admin_menu', function () {
    remove_submenu_page('hcdecor-hub','hcdecor-studio-v2');
    remove_submenu_page('hcdecor-hub','hcdecor-studio');
}, 999);

add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos((string)$hook, 'hcdecor-content-operations') === false) return;
    wp_enqueue_media();
});

function hcdecor_ops_fields() {
    return ['web_title','web_intro','web_body','seo_meta','facebook_caption','tiktok_script','youtube_title','youtube_description'];
}
function hcdecor_ops_statuses() {
    return ['draft'=>'Draft','review'=>'Review','approved'=>'Approved','published_web'=>'Published Web'];
}
function hcdecor_ops_get($id,$key,$default='') {
    $v=get_post_meta($id,'hc_'.$key,true);
    return $v===''?$default:$v;
}
function hcdecor_ops_save_fields($job_id,$src) {
    foreach(hcdecor_ops_fields() as $key){
        $value=isset($src[$key])?wp_unslash($src[$key]):'';
        update_post_meta($job_id,'hc_'.$key,sanitize_textarea_field($value));
    }
    $media=array_values(array_unique(array_filter(array_map('intval',(array)($src['media_ids']??[])))));
    update_post_meta($job_id,'hc_media_ids',$media);
    update_post_meta($job_id,'hc_cover_id',(int)($src['cover_id']??($media[0]??0)));
    update_post_meta($job_id,'hc_channels',array_values(array_intersect(['web','facebook','tiktok','youtube'],(array)($src['channels']??[]))));
    update_post_meta($job_id,'hc_outbound',false);
}

add_action('admin_post_hcdecor_ops_create', function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    check_admin_referer('hcdecor_ops_create');
    $project=(int)($_POST['project_id']??0);
    $brief=sanitize_textarea_field(wp_unslash($_POST['brief']??''));
    if(!$project || get_post_type($project)!=='hc_project') wp_die('Invalid project');
    $id=wp_insert_post([
        'post_type'=>'hc_content_job','post_status'=>'publish',
        'post_title'=>'Content · '.get_the_title($project).' · '.current_time('Y-m-d H:i'),
        'post_content'=>$brief
    ]);
    if(is_wp_error($id)) wp_die($id->get_error_message());
    update_post_meta($id,'hc_project_id',$project);
    update_post_meta($id,'hc_agent_status','draft');
    hcdecor_ops_save_fields($id,$_POST);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$id.'&created=1')); exit;
});

add_action('admin_post_hcdecor_ops_save', function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    $id=(int)($_POST['job_id']??0); check_admin_referer('hcdecor_ops_save_'.$id);
    if(get_post_type($id)!=='hc_content_job') wp_die('Invalid job');
    wp_update_post(['ID'=>$id,'post_content'=>sanitize_textarea_field(wp_unslash($_POST['brief']??''))]);
    hcdecor_ops_save_fields($id,$_POST);
    $status=sanitize_key($_POST['agent_status']??'draft');
    if(array_key_exists($status,hcdecor_ops_statuses())) update_post_meta($id,'hc_agent_status',$status);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$id.'&saved=1')); exit;
});

add_action('admin_post_hcdecor_ops_publish_web', function(){
    if(!current_user_can('publish_posts')) wp_die('Forbidden');
    $id=(int)($_POST['job_id']??0); check_admin_referer('hcdecor_ops_publish_'.$id);
    if(get_post_type($id)!=='hc_content_job') wp_die('Invalid job');
    if(hcdecor_ops_get($id,'agent_status')!=='approved') wp_die('Job must be approved first.');
    $project=(int)hcdecor_ops_get($id,'project_id');
    if(!$project || get_post_type($project)!=='hc_project') wp_die('Invalid project');
    $title=hcdecor_ops_get($id,'web_title',get_the_title($project));
    $intro=hcdecor_ops_get($id,'web_intro');
    $body=hcdecor_ops_get($id,'web_body');
    wp_update_post(['ID'=>$project,'post_title'=>$title,'post_excerpt'=>$intro,'post_content'=>$body]);
    $media=(array)hcdecor_ops_get($id,'media_ids',[]);
    update_post_meta($project,'hc_project_gallery',$media);
    $cover=(int)hcdecor_ops_get($id,'cover_id');
    if($cover && wp_attachment_is_image($cover)) set_post_thumbnail($project,$cover);
    update_post_meta($project,'hc_seo_meta',hcdecor_ops_get($id,'seo_meta'));
    update_post_meta($id,'hc_agent_status','published_web');
    update_post_meta($id,'hc_published_web_at',current_time('mysql'));
    update_post_meta($id,'hc_outbound',false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$id.'&published=1')); exit;
});

function hcdecor_ops_bridge_auth(){
    $token=(string)get_option('hcdecor_bridge_token','');
    $given=(string)($_SERVER['HTTP_X_HCDECOR_BRIDGE']??'');
    return $token && $given && hash_equals($token,$given);
}
add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)',[
        'methods'=>['GET','POST'],
        'permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $id=(int)$r['id']; if(get_post_type($id)!=='hc_content_job') return new WP_Error('not_found','Job not found',['status'=>404]);
            if($r->get_method()==='POST'){
                $p=$r->get_json_params()?:[];
                if(isset($p['brief'])) wp_update_post(['ID'=>$id,'post_content'=>sanitize_textarea_field($p['brief'])]);
                hcdecor_ops_save_fields($id,$p);
                if(isset($p['agent_status']) && array_key_exists($p['agent_status'],hcdecor_ops_statuses())) update_post_meta($id,'hc_agent_status',sanitize_key($p['agent_status']));
            }
            $post=get_post($id);
            $data=['id'=>$id,'title'=>$post->post_title,'brief'=>$post->post_content,'project_id'=>(int)hcdecor_ops_get($id,'project_id'),'status'=>hcdecor_ops_get($id,'agent_status','draft'),'media_ids'=>(array)hcdecor_ops_get($id,'media_ids',[]),'cover_id'=>(int)hcdecor_ops_get($id,'cover_id'),'channels'=>(array)hcdecor_ops_get($id,'channels',[]),'outbound'=>false];
            foreach(hcdecor_ops_fields() as $k) $data[$k]=hcdecor_ops_get($id,$k);
            return rest_ensure_response($data);
        }
    ]);
});

function hcdecor_ops_page(){
    if(!current_user_can('edit_posts')) return;
    $projects=get_posts(['post_type'=>'hc_project','post_status'=>['publish','draft'],'numberposts'=>100,'orderby'=>'modified','order'=>'DESC']);
    $job_id=(int)($_GET['job']??0); $job=$job_id&&get_post_type($job_id)==='hc_content_job'?get_post($job_id):null;
    $jobs=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>30,'orderby'=>'modified','order'=>'DESC']);
    $status=$job?hcdecor_ops_get($job_id,'agent_status','draft'):'draft';
    $media=$job?(array)hcdecor_ops_get($job_id,'media_ids',[]):[]; $cover=$job?(int)hcdecor_ops_get($job_id,'cover_id'):0;
    ?>
    <div class="wrap hcops"><style>
    .hcops{max-width:1500px}.hcops-head{display:flex;justify-content:space-between;align-items:center;margin:15px 0}.hcops-safe{background:#17191c;color:#e3ad69;border-radius:999px;padding:8px 12px;font-weight:700}.hcops-grid{display:grid;grid-template-columns:260px minmax(520px,1fr) 430px;gap:14px}.hcops-card{background:#fff;border:1px solid #dcdcde;border-radius:14px;overflow:hidden}.hcops-card h2{font-size:14px;margin:0;padding:14px 16px;border-bottom:1px solid #eee}.hcops-body{padding:14px}.hcops-job{display:block;padding:10px;border:1px solid #eee;border-radius:9px;margin-bottom:8px;text-decoration:none;color:#1d2327}.hcops-job.active{border-color:#c68a45;background:#fff9f2}.hcops-job small{display:block;color:#777;margin-top:3px}.hcops label{font-weight:600;display:block;margin:10px 0 5px}.hcops textarea,.hcops input[type=text],.hcops select{width:100%}.hcops textarea{min-height:95px}.hcops-media{display:grid;grid-template-columns:repeat(4,1fr);gap:7px;margin:9px 0}.hcops-media img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px}.hcops-preview{background:#0b1015;color:#fff;border-radius:12px;overflow:hidden}.hcops-cover{aspect-ratio:16/9;background:#20262c center/cover no-repeat;display:flex;align-items:end;padding:18px}.hcops-cover h3{font-size:26px;margin:0;text-shadow:0 2px 14px #000}.hcops-copy{padding:16px}.hcops-copy p{color:#c5ccd3}.hcops-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}.hcops-channels{display:flex;gap:10px;flex-wrap:wrap}.hcops-notice{background:#ecf7ed;border-left:4px solid #46b450;padding:10px 12px;margin:10px 0}@media(max-width:1200px){.hcops-grid{grid-template-columns:240px 1fr}.hcops-grid>section:last-child{grid-column:1/-1}}@media(max-width:782px){.hcops-grid{display:block}.hcops-card{margin-bottom:12px}}
    </style>
    <div class="hcops-head"><div><h1>HCDecor Content Operations</h1><p>Project → Media → Content Job → Review → Web Publish</p></div><span class="hcops-safe">SOCIAL OUTBOUND OFF</span></div>
    <?php if(isset($_GET['created'])||isset($_GET['saved'])||isset($_GET['published'])):?><div class="hcops-notice">Đã cập nhật.</div><?php endif;?>
    <div class="hcops-grid">
      <section class="hcops-card"><h2>CONTENT QUEUE</h2><div class="hcops-body">
        <?php foreach($jobs as $j): $s=hcdecor_ops_get($j->ID,'agent_status','draft');?><a class="hcops-job <?php echo $job_id===$j->ID?'active':'';?>" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-content-operations&job='.$j->ID));?>"><strong><?php echo esc_html($j->post_title);?></strong><small><?php echo esc_html(strtoupper($s));?></small></a><?php endforeach;?>
        <?php if(!$jobs):?><p>Chưa có Content Job.</p><?php endif;?>
      </div></section>
      <section class="hcops-card"><h2><?php echo $job?'EDIT CONTENT JOB':'NEW CONTENT JOB';?></h2><div class="hcops-body">
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
        <input type="hidden" name="action" value="<?php echo $job?'hcdecor_ops_save':'hcdecor_ops_create';?>">
        <?php if($job){wp_nonce_field('hcdecor_ops_save_'.$job_id);?><input type="hidden" name="job_id" value="<?php echo $job_id;?>"><?php }else{wp_nonce_field('hcdecor_ops_create');}?>
        <label>Project</label><select name="project_id" <?php echo $job?'disabled':'';?>><?php foreach($projects as $p):?><option value="<?php echo $p->ID;?>" <?php selected($job?(int)hcdecor_ops_get($job_id,'project_id'):0,$p->ID);?>><?php echo esc_html($p->post_title);?></option><?php endforeach;?></select>
        <?php if($job):?><input type="hidden" name="project_id" value="<?php echo (int)hcdecor_ops_get($job_id,'project_id');?>"><?php endif;?>
        <label>Media</label><input type="hidden" id="hcops_media_ids" name="media_ids[]" value=""><input type="hidden" id="hcops_cover_id" name="cover_id" value="<?php echo $cover;?>">
        <div class="hcops-media" id="hcops_media"><?php foreach($media as $mid){$u=wp_get_attachment_image_url($mid,'medium');if($u)echo '<img data-id="'.(int)$mid.'" src="'.esc_url($u).'">';}?></div>
        <button type="button" class="button" id="hcopsPick">Chọn / Upload Media</button>
        <div id="hcopsMediaHidden"><?php foreach($media as $mid):?><input type="hidden" name="media_ids[]" value="<?php echo (int)$mid;?>"><?php endforeach;?></div>
        <label>Agent brief</label><textarea name="brief" placeholder="Mục tiêu nội dung, phong cách, điểm cần nhấn mạnh..."><?php echo esc_textarea($job?$job->post_content:'');?></textarea>
        <div class="hcops-channels"><?php foreach(['web'=>'Web','facebook'=>'Facebook','tiktok'=>'TikTok / Reels','youtube'=>'YouTube'] as $k=>$v): $chs=$job?(array)hcdecor_ops_get($job_id,'channels',[]):['web'];?><label><input type="checkbox" name="channels[]" value="<?php echo $k;?>" <?php checked(in_array($k,$chs,true));?>> <?php echo $v;?></label><?php endforeach;?></div>
        <?php if($job):?><label>Status</label><select name="agent_status"><?php foreach(hcdecor_ops_statuses() as $k=>$v):?><option value="<?php echo $k;?>" <?php selected($status,$k);?>><?php echo $v;?></option><?php endforeach;?></select><?php endif;?>
        <label>Web title</label><input type="text" name="web_title" value="<?php echo esc_attr($job?hcdecor_ops_get($job_id,'web_title'):'');?>">
        <label>Web intro</label><textarea name="web_intro"><?php echo esc_textarea($job?hcdecor_ops_get($job_id,'web_intro'):'');?></textarea>
        <label>Web body</label><textarea name="web_body" style="min-height:180px"><?php echo esc_textarea($job?hcdecor_ops_get($job_id,'web_body'):'');?></textarea>
        <label>SEO meta</label><textarea name="seo_meta"><?php echo esc_textarea($job?hcdecor_ops_get($job_id,'seo_meta'):'');?></textarea>
        <label>Facebook caption</label><textarea name="facebook_caption"><?php echo esc_textarea($job?hcdecor_ops_get($job_id,'facebook_caption'):'');?></textarea>
        <label>TikTok / Reels script</label><textarea name="tiktok_script"><?php echo esc_textarea($job?hcdecor_ops_get($job_id,'tiktok_script'):'');?></textarea>
        <label>YouTube title</label><input type="text" name="youtube_title" value="<?php echo esc_attr($job?hcdecor_ops_get($job_id,'youtube_title'):'');?>">
        <label>YouTube description</label><textarea name="youtube_description"><?php echo esc_textarea($job?hcdecor_ops_get($job_id,'youtube_description'):'');?></textarea>
        <div class="hcops-actions"><button class="button button-primary"><?php echo $job?'Lưu Content Job':'Tạo Content Job';?></button></div>
      </form>
      <?php if($job && $status==='approved'):?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" class="hcops-actions"><?php wp_nonce_field('hcdecor_ops_publish_'.$job_id);?><input type="hidden" name="action" value="hcdecor_ops_publish_web"><input type="hidden" name="job_id" value="<?php echo $job_id;?>"><button class="button button-primary">Publish lên Web Project</button></form><?php endif;?>
      </div></section>
      <section class="hcops-card"><h2>WEB PREVIEW</h2><div class="hcops-body"><?php
        $ptitle=$job?hcdecor_ops_get($job_id,'web_title',get_the_title((int)hcdecor_ops_get($job_id,'project_id'))):'Chọn hoặc tạo Content Job';
        $pintro=$job?hcdecor_ops_get($job_id,'web_intro'):'Media và nội dung thật sẽ hiển thị tại đây.';
        $pbody=$job?hcdecor_ops_get($job_id,'web_body'):'';
        $cover_url=$cover?wp_get_attachment_image_url($cover,'large'):'';
      ?><div class="hcops-preview"><div class="hcops-cover" style="<?php echo $cover_url?'background-image:url('.esc_url($cover_url).')':'';?>"><h3><?php echo esc_html($ptitle);?></h3></div><div class="hcops-copy"><p><?php echo esc_html($pintro);?></p><div><?php echo wpautop(esc_html($pbody));?></div></div></div>
      <p><strong>Bridge API:</strong><br><code>/wp-json/hcdecor/v1/operations/jobs/{id}</code></p><p>Agent có thể đọc/ghi job qua Bridge token. Social outbound vẫn khóa.</p>
      </div></section>
    </div>
    <script>
    (function(){const pick=document.getElementById('hcopsPick');if(!pick)return;pick.onclick=function(){const frame=wp.media({title:'HCDecor Media',button:{text:'Dùng media đã chọn'},multiple:true});frame.on('select',function(){const items=frame.state().get('selection').toJSON();const grid=document.getElementById('hcops_media'),hidden=document.getElementById('hcopsMediaHidden');grid.innerHTML='';hidden.innerHTML='';items.forEach(function(a,i){const img=document.createElement('img');img.dataset.id=a.id;img.src=(a.sizes&&a.sizes.medium?a.sizes.medium.url:a.url);grid.appendChild(img);const h=document.createElement('input');h.type='hidden';h.name='media_ids[]';h.value=a.id;hidden.appendChild(h);if(i===0)document.getElementById('hcops_cover_id').value=a.id;});});frame.open();};})();
    </script></div><?php
}
