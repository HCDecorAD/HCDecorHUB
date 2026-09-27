<?php
if(!defined('ABSPATH')) exit;

function hcdecor_suite_sites(){ $v=get_option('hcdecor_suite_sites',[]); return is_array($v)?$v:[]; }
function hcdecor_suite_presets(){ $v=get_option('hcdecor_suite_presets',[]); return is_array($v)?$v:[]; }

add_action('admin_post_hcdecor_suite_site_save',function(){
 if(!current_user_can('manage_options'))wp_die('Forbidden'); check_admin_referer('hcdecor_suite_site_save');
 $v=hcdecor_suite_sites(); $id=sanitize_key($_POST['site_id']??''); if(!$id)$id='site_'.wp_generate_password(8,false,false);
 $v[$id]=['name'=>sanitize_text_field(wp_unslash($_POST['name']??'')),'url'=>esc_url_raw($_POST['url']??''),'group'=>sanitize_key($_POST['group']??''),'enabled'=>!empty($_POST['enabled'])];
 update_option('hcdecor_suite_sites',$v,false); wp_safe_redirect(admin_url('admin.php?page=hcdecor-suite')); exit;
});

add_action('admin_post_hcdecor_suite_preset_save',function(){
 if(!current_user_can('manage_options'))wp_die('Forbidden'); check_admin_referer('hcdecor_suite_preset_save');
 $v=hcdecor_suite_presets(); $id='preset_'.wp_generate_password(8,false,false);
 $v[$id]=['name'=>sanitize_text_field(wp_unslash($_POST['name']??'')),'account_group'=>sanitize_key($_POST['account_group']??''),'site_group'=>sanitize_key($_POST['site_group']??''),'schedule'=>sanitize_text_field($_POST['schedule']??''),'channels'=>array_values(array_intersect(['web','facebook','tiktok','youtube'],array_map('sanitize_key',(array)($_POST['channels']??[]))))];
 update_option('hcdecor_suite_presets',$v,false); wp_safe_redirect(admin_url('admin.php?page=hcdecor-suite')); exit;
});

add_action('admin_post_hcdecor_suite_pack',function(){
 if(!current_user_can('edit_posts'))wp_die('Forbidden'); check_admin_referer('hcdecor_suite_pack');
 $id=(int)($_POST['project_id']??0); $p=get_post($id);
 if($p&&$p->post_type==='hc_project'){
  $title=get_the_title($p); $base=sanitize_textarea_field(wp_unslash($_POST['brief']??''));
  if(!$base)$base=wp_strip_all_tags($p->post_excerpt?:$p->post_content);
  update_post_meta($id,'hc_content_pack',['web'=>['title'=>$title,'body'=>$base],'facebook'=>['caption'=>$title."\n\n".$base],'tiktok'=>['caption'=>$title.' · '.$base],'youtube'=>['title'=>$title,'description'=>$base]]);
 }
 wp_safe_redirect(admin_url('admin.php?page=hcdecor-suite')); exit;
});

function hcdecor_suite_stats(){
 $ids=get_posts(['post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>500,'fields'=>'ids']);
 $o=['total'=>count($ids),'done'=>0,'failed'=>0,'running'=>0,'queued'=>0];
 foreach($ids as $id){$s=(string)get_post_meta($id,'hc_auto_status',true);if(isset($o[$s]))$o[$s]++;}
 return $o;
}

add_action('admin_menu',function(){
 add_submenu_page('hcdecor-hub','HUB Control Center','Control Center','edit_posts','hcdecor-suite','hcdecor_suite_page',3);
},30);

function hcdecor_suite_page(){
 if(!current_user_can('edit_posts'))return;
 $sites=hcdecor_suite_sites(); $presets=hcdecor_suite_presets(); $stats=hcdecor_suite_stats();
 $projects=get_posts(['post_type'=>'hc_project','post_status'=>'publish','numberposts'=>50]);
 $groups=function_exists('hcdecor_social_groups')?hcdecor_social_groups():[];
 ?>
 <div class="wrap hcsuite"><style>
 .hcsuite{max-width:1500px}.hcs-kpi,.hcs-grid{display:grid;gap:12px}.hcs-kpi{grid-template-columns:repeat(4,1fr)}.hcs-grid{grid-template-columns:repeat(2,1fr);margin-top:12px}.hcs-card{background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:16px}.hcs-num{font-size:28px;font-weight:800}.hcs-flow{display:flex;gap:7px;flex-wrap:wrap;margin:15px 0}.hcs-flow span{background:#111827;color:#fff;padding:8px 11px;border-radius:20px}.hcsuite input,.hcsuite select,.hcsuite textarea{width:100%;margin:5px 0 9px}.hcsuite label{display:block}.hcsuite label input[type=checkbox]{width:auto}.hcs-mods{display:grid;grid-template-columns:repeat(5,1fr);gap:8px}.hcs-mod{padding:12px;border:1px solid #e5e7eb;border-radius:10px;background:#fafafa}@media(max-width:900px){.hcs-kpi,.hcs-grid,.hcs-mods{grid-template-columns:1fr 1fr}} </style>
 <h1>HCDecor HUB · Control Center</h1>
 <div class="hcs-flow"><?php foreach(['Project / Media','AI Content','Review','Websites','Social','Automation','CRM','Analytics'] as $x):?><span><?php echo esc_html($x);?></span><?php endforeach;?></div>
 <div class="hcs-kpi"><div class="hcs-card"><div class="hcs-num"><?php echo count($sites);?></div>Websites</div><div class="hcs-card"><div class="hcs-num"><?php echo count($presets);?></div>Campaign Presets</div><div class="hcs-card"><div class="hcs-num"><?php echo (int)$stats['done'];?></div>Completed</div><div class="hcs-card"><div class="hcs-num"><?php echo (int)$stats['failed'];?></div>Failed</div></div>
 <div class="hcs-grid">
 <section class="hcs-card"><h2>AI Content Pack</h2><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="hcdecor_suite_pack"><?php wp_nonce_field('hcdecor_suite_pack');?><select name="project_id"><?php foreach($projects as $p):?><option value="<?php echo $p->ID;?>"><?php echo esc_html($p->post_title);?></option><?php endforeach;?></select><textarea name="brief" rows="4" placeholder="Ý chính; để trống dùng nội dung Project"></textarea><button class="button button-primary">Tạo Pack Web + Social</button></form></section>
 <section class="hcs-card"><h2>Campaign Preset</h2><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="hcdecor_suite_preset_save"><?php wp_nonce_field('hcdecor_suite_preset_save');?><input name="name" placeholder="Tên chiến dịch"><select name="account_group"><option value="">Nhóm Social</option><?php foreach($groups as $k=>$n):?><option value="<?php echo esc_attr($k);?>"><?php echo esc_html($n);?></option><?php endforeach;?></select><input name="site_group" placeholder="Nhóm website"><input name="schedule" placeholder="Lịch mặc định"><?php foreach(['web','facebook','tiktok','youtube'] as $ch):?><label><input type="checkbox" name="channels[]" value="<?php echo $ch;?>" checked> <?php echo esc_html(strtoupper($ch));?></label><?php endforeach;?><p><button class="button button-primary">Lưu Preset</button></p></form></section>
 <section class="hcs-card"><h2>Multi-Website Manager</h2><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="hcdecor_suite_site_save"><?php wp_nonce_field('hcdecor_suite_site_save');?><input name="name" placeholder="Tên website"><input type="url" name="url" placeholder="Website URL"><input name="group" placeholder="Nhóm website"><label><input type="checkbox" name="enabled" checked> Active</label><p><button class="button button-primary">Thêm Website</button></p></form><table class="widefat striped"><tbody><?php foreach($sites as $s):?><tr><td><?php echo esc_html($s['name']);?></td><td><?php echo esc_html($s['group']);?></td><td><?php echo !empty($s['enabled'])?'Active':'Off';?></td></tr><?php endforeach;?></tbody></table></section>
 <section class="hcs-card"><h2>Automation & Analytics</h2><p>Queue: <strong><?php echo (int)$stats['total'];?></strong> · Running: <strong><?php echo (int)$stats['running'];?></strong> · Failed: <strong><?php echo (int)$stats['failed'];?></strong></p><p><a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-automation'));?>">Automation HUB</a> <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-social-manager'));?>">Social Manager</a></p></section>
 </div>
 <h2>HUB Expansion Modules</h2><div class="hcs-mods"><?php foreach(['Visual Workflow','Unified Inbox','CRM / Leads','AI Agent','Content Calendar','Integrations','Audit Log','Error Monitor','Conversion Funnel','Reports'] as $x):?><div class="hcs-mod"><strong><?php echo esc_html($x);?></strong><br><small>Module ready for adapter</small></div><?php endforeach;?></div>
 </div><?php
}
add_action('rest_api_init',function(){
 register_rest_route('hcdecor/v1','/suite/status',['methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth','callback'=>function(){return rest_ensure_response(['sites'=>count(hcdecor_suite_sites()),'presets'=>count(hcdecor_suite_presets()),'automation'=>hcdecor_suite_stats()]);}]);
});
