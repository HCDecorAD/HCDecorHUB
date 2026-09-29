<?php
if (!defined('ABSPATH')) exit;
const HCDECOR_IAM_VERSION='1.0.0';
function hcdecor_iam_roles(){
 add_role('hc_workspace_admin','HC Workspace Admin',['read'=>true,'edit_posts'=>true,'upload_files'=>true]);
 add_role('hc_store_admin','HC Store Admin',['read'=>true,'upload_files'=>true]);
 add_role('hc_staff','HC Staff',['read'=>true]);
 add_role('hc_viewer','HC Viewer',['read'=>true]);
}
add_action('init','hcdecor_iam_roles',2);
function hcdecor_iam_role($u=null){
 $u=$u?:wp_get_current_user();
 if(user_can($u,'manage_options')) return 'owner';
 foreach(['hc_workspace_admin','hc_store_admin','hc_staff','hc_viewer'] as $r) if(in_array($r,(array)$u->roles,true)) return $r;
 return 'viewer';
}
function hcdecor_iam_scope($uid=0){
 $uid=$uid?:get_current_user_id();
 return ['workspaces'=>(array)get_user_meta($uid,'hc_workspaces',true),'stores'=>(array)get_user_meta($uid,'hc_stores',true)];
}
function hcdecor_iam_can($workspace,$store=''){
 if(current_user_can('manage_options')) return true;
 $s=hcdecor_iam_scope(); if(!in_array($workspace,$s['workspaces'],true)) return false;
 return $store==='' || in_array($store,$s['stores'],true);
}
function hcdecor_audit($action,$context=[]){
 $row=['id'=>wp_generate_uuid4(),'at'=>current_time('mysql',true),'user_id'=>get_current_user_id(),'action'=>sanitize_key($action),'context'=>$context];
 $log=(array)get_option('hcdecor_audit_log',[]); array_unshift($log,$row); update_option('hcdecor_audit_log',array_slice($log,0,500),false); return $row;
}
function hcdecor_iam_me(){
 $u=wp_get_current_user(); return ['authenticated'=>is_user_logged_in(),'id'=>$u->ID,'name'=>$u->display_name,'role'=>hcdecor_iam_role($u),'scope'=>hcdecor_iam_scope($u->ID)];
}
function hcdecor_agent_execute_dag($req){
 $body=(array)$req->get_json_params(); $workspace=sanitize_key($body['workspace_id']??''); $store=sanitize_key($body['store_id']??'');
 $tasks=is_array($body['tasks']??null)?$body['tasks']:[]; if(!$workspace||!$tasks) return new WP_Error('invalid_dag','workspace_id and tasks required',['status'=>400]);
 if(!hcdecor_iam_can($workspace,$store)) return new WP_Error('forbidden_scope','Workspace/store access denied',['status'=>403]);
 $ids=[]; foreach($tasks as $t){$id=sanitize_key($t['task_id']??'');if(!$id||isset($ids[$id]))return new WP_Error('invalid_task','Task IDs must be unique',['status'=>400]);$ids[$id]=true;}
 foreach($tasks as $t)foreach((array)($t['depends_on']??[]) as $d)if(!isset($ids[sanitize_key($d)]))return new WP_Error('unknown_dependency','Unknown dependency',['status'=>400]);
 $done=[];$results=[];$remaining=$tasks;$guard=0;
 while($remaining&&$guard++<100){$progress=false;foreach($remaining as $k=>$t){$deps=array_map('sanitize_key',(array)($t['depends_on']??[]));if(array_diff($deps,$done))continue;
   $action=sanitize_key($t['action']??'view');$mutation=in_array($action,['create','edit','delete','publish','manage','deploy','rollback'],true);
   if($mutation&&empty($body['approved'])){$results[]=['task_id'=>sanitize_key($t['task_id']),'status'=>'blocked','reason'=>'approval_required'];}
   else{$payload=['workspace_id'=>$workspace,'store_id'=>$store,'module'=>sanitize_key($t['module']??''),'action'=>$action,'input'=>(array)($t['input']??[])];$value=apply_filters('hcdecor_agent_execute_task',['ok'=>true,'mode'=>$mutation?'authorized-mutation':'read','payload'=>$payload],$payload);$results[]=['task_id'=>sanitize_key($t['task_id']),'status'=>!empty($value['ok'])?'completed':'failed','result'=>$value];}
   $done[]=sanitize_key($t['task_id']);unset($remaining[$k]);$progress=true;
 } if(!$progress)break;}
 if($remaining)return new WP_Error('dependency_cycle','Dependency cycle detected',['status'=>400]);
 $audit=hcdecor_audit('agent_dag',['workspace_id'=>$workspace,'store_id'=>$store,'tasks'=>count($tasks),'results'=>array_column($results,'status')]);
 return ['ok'=>!in_array('failed',array_column($results,'status'),true),'dag_id'=>wp_generate_uuid4(),'workspace_id'=>$workspace,'store_id'=>$store,'results'=>$results,'audit_id'=>$audit['id']];
}
add_action('rest_api_init',function(){
 register_rest_route('hcdecor/v1','/iam/me',['methods'=>'GET','permission_callback'=>function(){return is_user_logged_in();},'callback'=>'hcdecor_iam_me']);
 register_rest_route('hcdecor/v1','/iam/users',['methods'=>'GET','permission_callback'=>function(){return current_user_can('manage_options');},'callback'=>function(){
  return array_map(function($u){return ['id'=>$u->ID,'name'=>$u->display_name,'email'=>$u->user_email,'role'=>hcdecor_iam_role($u),'scope'=>hcdecor_iam_scope($u->ID)];},get_users(['number'=>100]));
 }]);
 register_rest_route('hcdecor/v1','/iam/users',['methods'=>'POST','permission_callback'=>function(){return current_user_can('manage_options');},'callback'=>function($r){
  $b=(array)$r->get_json_params();$role=sanitize_key($b['role']??'hc_viewer');$allowed=['hc_workspace_admin','hc_store_admin','hc_staff','hc_viewer'];if(!in_array($role,$allowed,true))return new WP_Error('invalid_role','Invalid role',['status'=>400]);
  $id=wp_create_user(sanitize_user($b['username']??''),wp_generate_password(24,true,true),sanitize_email($b['email']??''));if(is_wp_error($id))return $id;
  (new WP_User($id))->set_role($role);update_user_meta($id,'hc_workspaces',array_values(array_map('sanitize_key',(array)($b['workspaces']??[]))));update_user_meta($id,'hc_stores',array_values(array_map('sanitize_key',(array)($b['stores']??[]))));
  hcdecor_audit('iam_user_created',['target_user'=>$id,'role'=>$role]);return new WP_REST_Response(['ok'=>true,'user_id'=>$id,'password_reset_required'=>true],201);
 }]);
 register_rest_route('hcdecor/v1','/audit',['methods'=>'GET','permission_callback'=>function(){return current_user_can('manage_options');},'callback'=>function(){return ['items'=>(array)get_option('hcdecor_audit_log',[])];}]);
 register_rest_route('hcdecor/v1','/agent/dag',['methods'=>'POST','permission_callback'=>function(){return is_user_logged_in();},'callback'=>'hcdecor_agent_execute_dag']);
});
add_action('admin_menu',function(){add_submenu_page('hcdecor-hub','HC Access & Agent','Access & Agent','manage_options','hcdecor-access-agent','hcdecor_iam_admin_page');},40);
function hcdecor_iam_admin_page(){
 $me=hcdecor_iam_me();$audit=array_slice((array)get_option('hcdecor_audit_log',[]),0,20);
 echo '<div class="wrap"><h1>HC Access & Agent</h1><p><b>Runtime:</b> IAM '.esc_html(HCDECOR_IAM_VERSION).' · default deny · workspace/store isolation · audit enabled</p>';
 echo '<h2>Current principal</h2><pre>'.esc_html(wp_json_encode($me,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)).'</pre><h2>Recent audit</h2><pre>'.esc_html(wp_json_encode($audit,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)).'</pre></div>';
}
add_filter('hcdecor_agent_execute_task',function($result,$task){
 $m=$task['module'];$a=$task['action'];
 if($a==='view'&&$m==='website') return ['ok'=>true,'mode'=>'live','data'=>['site'=>home_url('/'),'name'=>get_bloginfo('name'),'runtime'=>defined('HCDECOR_RUNTIME_VERSION')?HCDECOR_RUNTIME_VERSION:null]];
 if($a==='view'&&$m==='audit') return ['ok'=>true,'mode'=>'live','data'=>array_slice((array)get_option('hcdecor_audit_log',[]),0,50)];
 if($a==='view'&&in_array($m,['content','projects','media'],true)){
  $type=$m==='projects'?'hc_project':($m==='media'?'attachment':'hc_content_job');$counts=wp_count_posts($type);return ['ok'=>true,'mode'=>'live','data'=>(array)$counts];
 }
 if(in_array($a,['create','edit','delete','publish','manage'],true)){
  $hook='hcdecor_agent_mutation_'.$m.'_'.$a;if(!has_action($hook))return ['ok'=>false,'error'=>'mutation_handler_not_registered','hook'=>$hook];
  do_action($hook,$task);return ['ok'=>true,'mode'=>'mutation','hook'=>$hook];
 }
 return $result;
},10,2);
