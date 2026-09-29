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
    return ['draft'=>'Draft','processing'=>'Processing','review'=>'Review','approved'=>'Approved','published_web'=>'Published Web','failed'=>'Failed'];
}
function hcdecor_ops_get($id,$key,$default='') {
    $v=get_post_meta($id,'hc_'.$key,true);
    return $v===''?$default:$v;
}
function hcdecor_ops_valid_media_ids($ids,$limit=60) {
    $out=[];
    foreach(array_values(array_unique(array_filter(array_map('intval',(array)$ids)))) as $id){
        if(get_post_type($id)==='attachment') $out[]=$id;
        if(count($out)>=max(1,(int)$limit)) break;
    }
    return $out;
}
function hcdecor_ops_limit_text($value,$limit){
    $value=sanitize_textarea_field(is_scalar($value)?(string)$value:'');
    return function_exists('mb_substr')?mb_substr($value,0,$limit):substr($value,0,$limit);
}
function hcdecor_ops_save_fields($job_id,$src) {
    $job_id=(int)$job_id;
    if(!$job_id || get_post_type($job_id)!=='hc_content_job' || !is_array($src)) return false;
    $limits=['web_title'=>300,'web_intro'=>2000,'web_body'=>50000,'seo_meta'=>2000,'facebook_caption'=>10000,'tiktok_script'=>20000,'youtube_title'=>300,'youtube_description'=>20000];
    foreach(hcdecor_ops_fields() as $key){
        if(!array_key_exists($key,$src)) continue;
        $value=is_scalar($src[$key])?wp_unslash((string)$src[$key]):'';
        update_post_meta($job_id,'hc_'.$key,hcdecor_ops_limit_text($value,(int)($limits[$key]??10000)));
    }
    $stored_media=get_post_meta($job_id,'hc_media_ids',true);$media=array_key_exists('media_ids',$src)?hcdecor_ops_valid_media_ids($src['media_ids']):hcdecor_ops_valid_media_ids(is_array($stored_media)?$stored_media:[]);
    if(array_key_exists('media_ids',$src)) update_post_meta($job_id,'hc_media_ids',$media);
    if(array_key_exists('cover_id',$src)){
        $cover=(int)$src['cover_id'];
        update_post_meta($job_id,'hc_cover_id',($cover && in_array($cover,$media,true))?$cover:($media?(int)$media[0]:0));
    }elseif(array_key_exists('media_ids',$src)){
        update_post_meta($job_id,'hc_cover_id',$media?(int)$media[0]:0);
    }
    if(array_key_exists('channels',$src)) update_post_meta($job_id,'hc_channels',array_values(array_intersect(['web','facebook','tiktok','youtube'],is_array($src['channels'])?$src['channels']:[])));
    update_post_meta($job_id,'hc_outbound',false);
    return true;
}

add_action('admin_post_hcdecor_ops_create', function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    check_admin_referer('hcdecor_ops_create');
    $project=(int)($_POST['project_id']??0);
    $brief=hcdecor_ops_limit_text(wp_unslash($_POST['brief']??''),20000);
    if(!$project || get_post_type($project)!=='hc_project' || !current_user_can('edit_post',$project)) wp_die('Invalid project');
    $id=wp_insert_post([
        'post_type'=>'hc_content_job','post_status'=>'publish',
        'post_title'=>'Content · '.get_the_title($project).' · '.current_time('Y-m-d H:i'),
        'post_content'=>$brief
    ]);
    if(is_wp_error($id)) wp_die(function_exists('hcdecor_ai_safe_error')?hcdecor_ai_safe_error($id->get_error_message()):'Unable to create content job.');
    update_post_meta($id,'hc_project_id',$project);
    if(!function_exists('hcdecor_workflow_set_status') || !hcdecor_workflow_set_status($id,'draft','Created from Content Operations')){
        wp_delete_post($id,true);
        wp_die('Workflow engine unavailable.');
    }
    hcdecor_ops_save_fields($id,$_POST);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$id.'&created=1')); exit;
});

add_action('admin_post_hcdecor_ops_save', function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    $id=(int)($_POST['job_id']??0); check_admin_referer('hcdecor_ops_save_'.$id);
    if(get_post_type($id)!=='hc_content_job' || !current_user_can('edit_post',$id)) wp_die('Invalid job');
    $status=sanitize_key($_POST['agent_status']??'');
    $current=sanitize_key(hcdecor_ops_limit_text(get_post_meta($id,'hc_agent_status',true),50));
    if($status!=='' && $status!==$current) wp_die('Workflow status is read-only here. Use Review Center or worker lifecycle actions.');
    if(in_array($current,['approved','published_web'],true)) wp_die('Approved or published jobs are immutable here. Return the job for changes and review again.');
    wp_update_post(['ID'=>$id,'post_content'=>hcdecor_ops_limit_text(wp_unslash($_POST['brief']??''),20000)]);
    hcdecor_ops_save_fields($id,$_POST);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$id.'&saved=1')); exit;
});

add_action('admin_post_hcdecor_ops_publish_web', function(){
    if(!current_user_can('publish_posts')) wp_die('Forbidden');
    $id=(int)($_POST['job_id']??0); check_admin_referer('hcdecor_ops_publish_'.$id);
    if(get_post_type($id)!=='hc_content_job' || !current_user_can('edit_post',$id)) wp_die('Invalid job');
    if((string)($_POST['production_approved']??'')!=='1') wp_die('Explicit production publish approval is required.');
    if(!function_exists('hcdecor_publish_job_to_web')) wp_die('Web publisher unavailable');
    update_post_meta($id,'hc_publish_approved_by',get_current_user_id());
    update_post_meta($id,'hc_publish_approval_source','wp_user');
    update_post_meta($id,'hc_publish_approved_at',current_time('mysql'));
    $r=hcdecor_publish_job_to_web($id);
    if(is_wp_error($r)){
        delete_post_meta($id,'hc_publish_approved_by');
        delete_post_meta($id,'hc_publish_approved_at');
        delete_post_meta($id,'hc_publish_approval_source');
        wp_die(function_exists('hcdecor_ai_safe_error')?hcdecor_ai_safe_error($r->get_error_message()):'Web publish failed.');
    }
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$id.'&published=1')); exit;
});

function hcdecor_ops_bridge_tokens(){
    $tokens=[];
    if(defined('HCDECOR_OPS_API_TOKEN') && is_scalar(HCDECOR_OPS_API_TOKEN)) $tokens[]=trim((string)HCDECOR_OPS_API_TOKEN);
    $env=getenv('HCDECOR_OPS_API_TOKEN');if(is_string($env)) $tokens[]=trim($env);
    return array_values(array_unique(array_filter($tokens,function($token){$len=strlen($token);return $len>=32 && $len<=512;})));
}
function hcdecor_ops_bridge_configured(){ return count(hcdecor_ops_bridge_tokens())>0; }
function hcdecor_ops_bridge_auth($request=null){
    if(is_user_logged_in() && current_user_can('manage_options')) return true;
    if($request instanceof WP_REST_Request && !in_array($request->get_method(),['GET','HEAD'],true)){
        $raw=(string)$request->get_header('content-length');
        if($raw==='' || !ctype_digit($raw) || (int)$raw<1 || (int)$raw>65536) return false;
    }
    $given=(string)($_SERVER['HTTP_X_HCDECOR_BRIDGE']??'');
    if($given===''){
        $auth=(string)($_SERVER['HTTP_AUTHORIZATION']??'');
        if(stripos($auth,'Bearer ')===0) $given=trim(substr($auth,7));
    }
    if($given==='' || strlen($given)>512) return false;
    foreach(hcdecor_ops_bridge_tokens() as $token){
        if(hash_equals($token,$given)) return true;
    }
    return false;
}
add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/operations/queue',[
        'methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $status_raw=$r->get_param('status');$status=sanitize_key(is_scalar($status_raw)?(string)$status_raw:'');
            if($status!=='' && !array_key_exists($status,hcdecor_ops_statuses())) return new WP_Error('status','Invalid workflow status.',['status'=>400]);
            $meta=$status?[['key'=>'hc_agent_status','value'=>$status]]:[];
            $jobs=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>50,'orderby'=>'modified','order'=>'ASC','meta_query'=>$meta]);
            $out=[]; foreach($jobs as $j){$out[]=['id'=>$j->ID,'title'=>hcdecor_ops_limit_text($j->post_title,500),'brief'=>hcdecor_ops_limit_text($j->post_content,20000),'project_id'=>(int)hcdecor_ops_get($j->ID,'project_id'),'status'=>sanitize_key((string)hcdecor_ops_get($j->ID,'agent_status','draft')),'channels'=>array_values(array_intersect(['web','facebook','tiktok','youtube'],(array)hcdecor_ops_get($j->ID,'channels',[]))),'media_ids'=>hcdecor_ops_valid_media_ids(hcdecor_ops_get($j->ID,'media_ids',[])),'outbound'=>false];}
            return rest_ensure_response($out);
        }
    ]);

    register_rest_route('hcdecor/v1','/operations/jobs/(?P<id>\d+)',[
        'methods'=>['GET','POST'],
        'permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $id=(int)$r['id']; if(get_post_type($id)!=='hc_content_job') return new WP_Error('not_found','Job not found',['status'=>404]);
            if(get_current_user_id()>0 && !current_user_can('edit_post',$id)) return new WP_Error('forbidden','Forbidden.',['status'=>403]);
            if($r->get_method()==='POST'){
                $current=sanitize_key(hcdecor_ops_limit_text(get_post_meta($id,'hc_agent_status',true),50));
                if(in_array($current,['approved','published_web'],true)) return new WP_Error('immutable','Approved or published jobs must be returned for changes before editing',['status'=>409]);
                $p=$r->get_json_params()?:[];
                if(isset($p['agent_status'])){ $agent_status=sanitize_key($p['agent_status']); $current=sanitize_key(hcdecor_ops_limit_text(get_post_meta($id,'hc_agent_status',true),50)); if($agent_status!==$current) return new WP_Error('status_read_only','Workflow status is read-only on this endpoint; use worker lifecycle routes',['status'=>409]); }
                if(isset($p['brief'])) wp_update_post(['ID'=>$id,'post_content'=>hcdecor_ops_limit_text(is_scalar($p['brief'])?(string)$p['brief']:'',20000)]);
                hcdecor_ops_save_fields($id,$p);
            }
            $post=get_post($id);
            $data=['id'=>$id,'title'=>hcdecor_ops_limit_text($post->post_title,500),'brief'=>hcdecor_ops_limit_text($post->post_content,20000),'project_id'=>(int)hcdecor_ops_get($id,'project_id'),'status'=>sanitize_key((string)hcdecor_ops_get($id,'agent_status','draft')),'media_ids'=>hcdecor_ops_valid_media_ids(hcdecor_ops_get($id,'media_ids',[])),'cover_id'=>(int)hcdecor_ops_get($id,'cover_id'),'channels'=>array_values(array_intersect(['web','facebook','tiktok','youtube'],(array)hcdecor_ops_get($id,'channels',[]))),'outbound'=>false];
            foreach(hcdecor_ops_fields() as $k) $data[$k]=hcdecor_ops_limit_text(hcdecor_ops_get($id,$k),50000);
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
        <?php if($job):?><label>Status</label><input type="hidden" name="agent_status" value="<?php echo esc_attr($status);?>"><p><strong><?php echo esc_html(hcdecor_ops_statuses()[$status]??ucfirst($status));?></strong> <small>— change in Review Center / worker lifecycle</small></p><?php endif;?>
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
      <?php if($job && $status==='approved'):?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" class="hcops-actions"><?php wp_nonce_field('hcdecor_ops_publish_'.$job_id);?><input type="hidden" name="action" value="hcdecor_ops_publish_web"><input type="hidden" name="job_id" value="<?php echo $job_id;?>"><label style="display:block;margin:8px 0"><input type="checkbox" name="production_approved" value="1" required> Tôi xác nhận thao tác này sẽ cập nhật nội dung production public.</label><button class="button button-primary">Publish lên Web Project</button></form><?php endif;?>
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
