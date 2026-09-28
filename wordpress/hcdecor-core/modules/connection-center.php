<?php
if (!defined('ABSPATH')) exit;
function hcdecor_conn_get(){ $v=get_option('hcdecor_connections',array()); return is_array($v)?array_slice($v,-100,null,true):array(); }
function hcdecor_conn_save($v){ update_option('hcdecor_connections',array_slice((array)$v,-100,null,true),false); }
function hcdecor_conn_safe($x){ unset($x['secret'],$x['token'],$x['client_secret'],$x['password']); return $x; }
function hcdecor_conn_status($x){ if(empty($x['enabled'])) return 'disabled'; if(empty($x['endpoint'])) return 'action_required'; return 'configured'; }
function hcdecor_conn_live_health($x){ if(empty($x['enabled']) || empty($x['endpoint'])) return 'not_checked'; if(!empty($x['last_error'])) return 'unreachable'; return !empty($x['last_ok'])?'healthy':'not_checked'; }
function hcdecor_conn_public_https($url){
 $p=wp_parse_url((string)$url); if(!is_array($p)||strtolower((string)($p['scheme']??''))!=='https'||empty($p['host'])) return false;
 $host=strtolower((string)$p['host']); if($host==='localhost'||substr($host,-6)==='.local'||substr($host,-9)==='.internal') return false;
 $ips=[];
 if(filter_var($host,FILTER_VALIDATE_IP)) $ips=[$host]; else {
  $v4=@gethostbynamel($host); if(is_array($v4)) $ips=array_merge($ips,$v4);
  if(function_exists('dns_get_record') && defined('DNS_AAAA')){
   $v6=@dns_get_record($host,DNS_AAAA);
   if(is_array($v6)) foreach($v6 as $row){ if(!empty($row['ipv6'])) $ips[]=$row['ipv6']; }
  }
 }
 if(!$ips) return false;
 foreach(array_unique($ips) as $ip){ if(!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)) return false; }
 return true;
}
add_action('admin_post_hcdecor_conn_save',function(){
 if(!current_user_can('manage_options')) wp_die('Forbidden'); check_admin_referer('hcdecor_conn_save'); $all=hcdecor_conn_get();
 $id=sanitize_key(isset($_POST['id'])?$_POST['id']:''); if(!$id) $id='conn_'.wp_generate_password(8,false,false); $old=isset($all[$id])?$all[$id]:array();
 $secret=trim((string)wp_unslash(isset($_POST['secret'])?$_POST['secret']:'')); if($secret==='') $secret=isset($old['secret'])?$old['secret']:'';
 $name=sanitize_text_field(wp_unslash(isset($_POST['name'])?$_POST['name']:''));
 $provider=sanitize_key(isset($_POST['provider'])?$_POST['provider']:'');
 $endpoint=esc_url_raw(isset($_POST['endpoint'])?$_POST['endpoint']:'');
 if($endpoint!=='' && !hcdecor_conn_public_https($endpoint)) wp_die('Connection endpoint must be a public HTTPS URL.');
 $account=sanitize_text_field(wp_unslash(isset($_POST['account'])?$_POST['account']:''));
 $same_probe=isset($old['provider'],$old['endpoint'],$old['account']) && $old['provider']===$provider && $old['endpoint']===$endpoint && $old['account']===$account && hash_equals((string)($old['secret']??''),(string)$secret);
 $all[$id]=array('name'=>$name,'provider'=>$provider,'endpoint'=>$endpoint,'account'=>$account,'secret'=>$secret,'enabled'=>!empty($_POST['enabled']),'last_ok'=>$same_probe?(isset($old['last_ok'])?$old['last_ok']:''):'','last_error'=>$same_probe?(isset($old['last_error'])?$old['last_error']:''):'');
 hcdecor_conn_save($all); wp_safe_redirect(admin_url('admin.php?page=hcdecor-connections')); exit;
});
add_action('admin_post_hcdecor_conn_test',function(){
 if(!current_user_can('manage_options')) wp_die('Forbidden'); $id=sanitize_key(isset($_GET['id'])?$_GET['id']:''); check_admin_referer('hcdecor_conn_test_'.$id); $all=hcdecor_conn_get();
 if(isset($all[$id])){ $x=$all[$id]; $url=isset($x['endpoint'])?$x['endpoint']:''; if($url){ if(!hcdecor_conn_public_https($url)) wp_die('Unsafe connection endpoint.'); $res=wp_safe_remote_get($url,array('timeout'=>10,'redirection'=>2,'user-agent'=>'HCDecor-HUB/1.0')); if(is_wp_error($res)) $all[$id]['last_error']=$res->get_error_message(); else { $code=wp_remote_retrieve_response_code($res); if($code>=200&&$code<300){$all[$id]['last_ok']=current_time('mysql');$all[$id]['last_error']='';}else{$all[$id]['last_ok']='';$all[$id]['last_error']='HTTP '.$code;} } } hcdecor_conn_save($all); }
 wp_safe_redirect(admin_url('admin.php?page=hcdecor-connections')); exit;
});
add_action('admin_menu',function(){ add_submenu_page('hcdecor-hub','Connection Center','Connections','manage_options','hcdecor-connections','hcdecor_conn_page',4); },29);
function hcdecor_conn_page(){ if(!current_user_can('manage_options')) return; $all=hcdecor_conn_get(); echo '<div class="wrap"><h1>HCDecor HUB - Connection Center</h1><p>Secrets are stored server-side.</p><h2>Connections: '.count($all).'</h2></div>'; }
add_action('rest_api_init',function(){ register_rest_route('hcdecor/v1','/connections',array('methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth','callback'=>function(){ $o=array(); foreach(hcdecor_conn_get() as $id=>$x){$o[$id]=hcdecor_conn_safe($x);$o[$id]['status']=hcdecor_conn_status($x);$o[$id]['live_health']=hcdecor_conn_live_health($x);} return rest_ensure_response($o); })); });
