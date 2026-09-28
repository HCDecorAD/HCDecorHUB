<?php
if (!defined('ABSPATH')) exit;
function hcdecor_ops_flows(){ $v=get_option('hcdecor_ops_flows',array()); return is_array($v)?array_slice($v,-100,null,true):array(); }
function hcdecor_ops_inbox(){ $v=get_option('hcdecor_ops_inbox',array()); return is_array($v)?array_slice($v,-500,null,true):array(); }
function hcdecor_ops_save_flows($v){ update_option('hcdecor_ops_flows',array_slice((array)$v,-100,null,true),false); }
function hcdecor_ops_save_inbox($v){ update_option('hcdecor_ops_inbox',array_slice((array)$v,-500,null,true),false); }
function hcdecor_crm_text($value,$limit=500){
 $text=sanitize_text_field(wp_unslash((string)$value));
 return function_exists('mb_substr')?mb_substr($text,0,$limit):substr($text,0,$limit);
}
function hcdecor_crm_textarea($value,$limit=5000){
 $text=sanitize_textarea_field(wp_unslash((string)$value));
 return function_exists('mb_substr')?mb_substr($text,0,$limit):substr($text,0,$limit);
}
add_action('admin_post_hcdecor_ops_flow_save',function(){
 if(!current_user_can('manage_options')) wp_die('Forbidden'); check_admin_referer('hcdecor_ops_flow_save');
 $v=hcdecor_ops_flows(); $id='flow_'.wp_generate_password(8,false,false);
 $trigger=sanitize_key(isset($_POST['trigger'])?$_POST['trigger']:'');
 $action=sanitize_key(isset($_POST['flow_action'])?$_POST['flow_action']:'');
 if(!in_array($trigger,array('project_created','content_approved','lead_created','schedule'),true)) wp_die('Invalid workflow trigger');
 if(!in_array($action,array('ai_content','web_publish','social_publish','notify'),true)) wp_die('Invalid workflow action');
 $v[$id]=array('name'=>hcdecor_crm_text(isset($_POST['name'])?$_POST['name']:'',160),'trigger'=>$trigger,'filter'=>hcdecor_crm_text(isset($_POST['filter'])?$_POST['filter']:'',500),'action'=>$action,'enabled'=>!empty($_POST['enabled']));
 hcdecor_ops_save_flows($v); wp_safe_redirect(admin_url('admin.php?page=hcdecor-ops')); exit;
});
add_action('admin_post_hcdecor_ops_inbox_add',function(){
 if(!current_user_can('edit_posts')) wp_die('Forbidden'); check_admin_referer('hcdecor_ops_inbox_add');
 $v=hcdecor_ops_inbox(); $id='msg_'.wp_generate_password(8,false,false);
 $source=sanitize_key(isset($_POST['source'])?$_POST['source']:'manual');
 if(!in_array($source,array('website','facebook','tiktok','zalo','manual'),true)) wp_die('Invalid inbox source');
 $v[$id]=array('name'=>hcdecor_crm_text(isset($_POST['name'])?$_POST['name']:'',160),'source'=>$source,'contact'=>hcdecor_crm_text(isset($_POST['contact'])?$_POST['contact']:'',240),'message'=>hcdecor_crm_textarea(isset($_POST['message'])?$_POST['message']:'',5000),'status'=>'new','at'=>current_time('mysql'));
 hcdecor_ops_save_inbox($v); wp_safe_redirect(admin_url('admin.php?page=hcdecor-ops')); exit;
});
add_action('admin_post_hcdecor_ops_to_lead',function(){
 if(!current_user_can('edit_posts')) wp_die('Forbidden'); $id=sanitize_key(isset($_GET['id'])?$_GET['id']:''); check_admin_referer('hcdecor_ops_to_lead_'.$id);
 $v=hcdecor_ops_inbox();
 if(isset($v[$id])){ $x=$v[$id]; $name=!empty($x['name'])?$x['name']:'Lead'; $lead=wp_insert_post(array('post_type'=>'hc_lead','post_status'=>'publish','post_title'=>$name,'post_content'=>$x['message']));
  if($lead && !is_wp_error($lead)){ update_post_meta($lead,'hc_status','new'); update_post_meta($lead,'hc_source',$x['source']); update_post_meta($lead,'hc_phone',$x['contact']); $v[$id]['status']='lead'; hcdecor_ops_save_inbox($v); }
 }
 wp_safe_redirect(admin_url('admin.php?page=hcdecor-ops')); exit;
});
add_action('admin_menu',function(){ add_submenu_page('hcdecor-hub','Workflow & CRM','Workflow & CRM','edit_posts','hcdecor-ops','hcdecor_crm_page',7); },31);
function hcdecor_crm_page(){
 if(!current_user_can('edit_posts')) return; $flows=hcdecor_ops_flows(); $inbox=hcdecor_ops_inbox(); $counts=wp_count_posts('hc_lead'); $leads=isset($counts->publish)?(int)$counts->publish:0;
 echo '<div class="wrap"><h1>Workflow & CRM</h1><p>Workflow foundation - Unified Inbox - Lead Pipeline</p><div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">';
 echo '<section style="background:#fff;padding:16px"><h2>Workflow Builder</h2><form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="hcdecor_ops_flow_save">'; wp_nonce_field('hcdecor_ops_flow_save');
 echo '<input name="name" placeholder="Workflow name"><select name="trigger"><option value="project_created">Project created</option><option value="content_approved">Content approved</option><option value="lead_created">Lead created</option><option value="schedule">Schedule</option></select><input name="filter" placeholder="Filter"><select name="flow_action"><option value="ai_content">AI Content</option><option value="web_publish">Web Publish</option><option value="social_publish">Social Publish</option><option value="notify">Notify</option></select><label><input type="checkbox" name="enabled"> Enabled</label><p><button class="button button-primary">Save Workflow</button></p></form>';
 foreach($flows as $f){ echo '<p><code>'.esc_html($f['trigger']).' -> '.esc_html($f['filter']).' -> '.esc_html($f['action']).'</code></p>'; } echo '</section>';
 echo '<section style="background:#fff;padding:16px"><h2>Unified Inbox</h2><form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="hcdecor_ops_inbox_add">'; wp_nonce_field('hcdecor_ops_inbox_add');
 echo '<input name="name" placeholder="Customer"><select name="source"><option value="website">Website</option><option value="facebook">Facebook</option><option value="tiktok">TikTok</option><option value="zalo">Zalo</option><option value="manual">Manual</option></select><input name="contact" placeholder="Contact"><textarea name="message"></textarea><button class="button">Add Inbox</button></form><hr>';
 foreach(array_reverse($inbox,true) as $id=>$x){ echo '<p><strong>'.esc_html($x['name']).'</strong> - '.esc_html($x['source']).' - '.esc_html($x['status']).'</p>'; } echo '</section>';
 echo '<section style="background:#fff;padding:16px"><h2>CRM Pipeline</h2><p><strong>'.$leads.'</strong> leads</p><a class="button" href="'.esc_url(admin_url('admin.php?page=hcdecor-pipeline')).'">Open Pipeline</a></section>';
 echo '<section style="background:#fff;padding:16px"><h2>Automation Bridge</h2><a class="button" href="'.esc_url(admin_url('admin.php?page=hcdecor-automation')).'">Automation HUB</a></section></div></div>';
}
