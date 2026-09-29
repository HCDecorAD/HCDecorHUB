<?php
if (!defined('ABSPATH')) exit;
function hcdecor_conn_clip($value,$limit){ $value=is_scalar($value)?(string)$value:''; return function_exists('mb_substr')?mb_substr($value,0,$limit):substr($value,0,$limit); }
function hcdecor_conn_normalize($x){ $x=is_array($x)?$x:array(); return array('name'=>hcdecor_conn_clip($x['name']??'',500),'provider'=>hcdecor_conn_clip(sanitize_key(hcdecor_conn_clip($x['provider']??'',100)),100),'endpoint'=>hcdecor_conn_clip(esc_url_raw(hcdecor_conn_clip($x['endpoint']??'',2048)),2048),'account'=>hcdecor_conn_clip($x['account']??'',500),'secret'=>hcdecor_conn_clip($x['secret']??'',8192),'enabled'=>!empty($x['enabled']),'last_ok'=>hcdecor_conn_clip($x['last_ok']??'',64),'last_error'=>hcdecor_conn_clip($x['last_error']??'',500)); }
function hcdecor_conn_get(){ $v=get_option('hcdecor_connections',array());$out=array();if(!is_array($v))return $out;foreach(array_slice($v,-100,null,true) as $id=>$x){$safe_id=hcdecor_conn_clip(sanitize_key((string)$id),64);if($safe_id!=='')$out[$safe_id]=hcdecor_conn_normalize($x);}return $out; }
function hcdecor_conn_save($v){ $out=array();foreach(array_slice((array)$v,-100,null,true) as $id=>$x){$safe_id=hcdecor_conn_clip(sanitize_key((string)$id),64);if($safe_id!=='')$out[$safe_id]=hcdecor_conn_normalize($x);}update_option('hcdecor_connections',$out,false); }
function hcdecor_conn_safe_error($message){
 $text=sanitize_text_field(is_scalar($message)?(string)$message:'');
 $text=preg_replace('/\bBearer\s+[A-Za-z0-9._~+\/-]{12,}\b/i','[REDACTED]',$text);
 $text=preg_replace('/([?&](?:token|key|api[_-]?key|secret|sig|signature|code)=)[^&\s]+/i','$1[REDACTED]',$text);
 return function_exists('mb_substr')?mb_substr($text,0,500):substr($text,0,500);
}
function hcdecor_conn_safe_endpoint($url){
 $p=wp_parse_url((string)$url);
 if(!is_array($p) || empty($p['scheme']) || empty($p['host'])) return '';
 $origin=strtolower((string)$p['scheme']).'://'.strtolower((string)$p['host']);
 if(!empty($p['port'])) $origin.=':'.(int)$p['port'];
 return $origin;
}
function hcdecor_conn_safe($x){
 $safe=(array)$x;
 $safe['endpoint']=hcdecor_conn_safe_endpoint($safe['endpoint']??'');
 $safe['has_secret']=!empty($safe['secret']);
 unset($safe['secret'],$safe['token'],$safe['client_secret'],$safe['password']);
 if(isset($safe['last_error'])) $safe['last_error']=hcdecor_conn_safe_error($safe['last_error']);
 return $safe;
}
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
 $id_raw=$_POST['id']??'';$id=hcdecor_conn_clip(sanitize_key(is_scalar($id_raw)?(string)$id_raw:''),64); if(!$id) $id='conn_'.wp_generate_password(8,false,false); $old=isset($all[$id])?$all[$id]:array();
 $secret_input=wp_unslash(isset($_POST['secret'])?$_POST['secret']:'');$secret_raw=is_scalar($secret_input)?trim((string)$secret_input):''; if(strlen($secret_raw)>8192) wp_die('Connection secret is too long.'); $secret=hcdecor_conn_clip($secret_raw,8192); if($secret==='') $secret=hcdecor_conn_clip($old['secret']??'',8192);
 $name_raw=wp_unslash($_POST['name']??'');$name=hcdecor_conn_clip(sanitize_text_field(is_scalar($name_raw)?(string)$name_raw:''),500);
 $provider_raw=$_POST['provider']??'';$provider=hcdecor_conn_clip(sanitize_key(is_scalar($provider_raw)?(string)$provider_raw:''),100);
 $endpoint_raw=$_POST['endpoint']??'';$endpoint=hcdecor_conn_clip(esc_url_raw(is_scalar($endpoint_raw)?(string)$endpoint_raw:''),2048);
 if($endpoint!=='' && !hcdecor_conn_public_https($endpoint)) wp_die('Connection endpoint must be a public HTTPS URL.');
 $account_raw=wp_unslash($_POST['account']??'');$account=hcdecor_conn_clip(sanitize_text_field(is_scalar($account_raw)?(string)$account_raw:''),500);
 $same_probe=isset($old['provider'],$old['endpoint'],$old['account']) && $old['provider']===$provider && $old['endpoint']===$endpoint && $old['account']===$account && hash_equals((string)($old['secret']??''),(string)$secret);
 $all[$id]=array('name'=>$name,'provider'=>$provider,'endpoint'=>$endpoint,'account'=>$account,'secret'=>$secret,'enabled'=>!empty($_POST['enabled']),'last_ok'=>$same_probe?(isset($old['last_ok'])?$old['last_ok']:''):'','last_error'=>$same_probe?(isset($old['last_error'])?$old['last_error']:''):'');
 hcdecor_conn_save($all); wp_safe_redirect(admin_url('admin.php?page=hcdecor-connections')); exit;
});
add_action('admin_post_hcdecor_conn_test',function(){
 if(!current_user_can('manage_options')) wp_die('Forbidden'); $id=hcdecor_conn_clip(sanitize_key(isset($_GET['id'])?$_GET['id']:''),64); check_admin_referer('hcdecor_conn_test_'.$id); $all=hcdecor_conn_get();
 if(isset($all[$id])){ $x=$all[$id]; $url=isset($x['endpoint'])?$x['endpoint']:''; if($url){ if(!hcdecor_conn_public_https($url)) wp_die('Unsafe connection endpoint.'); $args=array('timeout'=>10,'redirection'=>0,'limit_response_size'=>64*1024,'user-agent'=>'HCDecor-HUB/1.0','headers'=>array()); if(!empty($x['secret'])) $args['headers']['Authorization']='Bearer '.$x['secret']; $res=wp_safe_remote_get($url,$args); if(is_wp_error($res)) $all[$id]['last_error']=hcdecor_conn_safe_error($res->get_error_message()); else { $code=wp_remote_retrieve_response_code($res); if($code>=200&&$code<300){$all[$id]['last_ok']=current_time('mysql');$all[$id]['last_error']='';}else{$all[$id]['last_ok']='';$all[$id]['last_error']='HTTP '.$code;} } } hcdecor_conn_save($all); }
 wp_safe_redirect(admin_url('admin.php?page=hcdecor-connections')); exit;
});
add_action('admin_menu',function(){ add_submenu_page('hcdecor-hub','Connection Center','Connections','manage_options','hcdecor-connections','hcdecor_conn_page',4); },29);
function hcdecor_conn_page(){ if(!current_user_can('manage_options')) return; $all=hcdecor_conn_get(); echo '<div class="wrap"><h1>HCDecor HUB - Connection Center</h1><p>Secrets are stored server-side.</p><h2>Connections: '.count($all).'</h2></div>'; }
add_action('rest_api_init',function(){ register_rest_route('hcdecor/v1','/connections',array('methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth','callback'=>function(){ $o=array(); foreach(hcdecor_conn_get() as $id=>$x){$o[$id]=hcdecor_conn_safe($x);$o[$id]['status']=hcdecor_conn_status($x);$o[$id]['live_health']=hcdecor_conn_live_health($x);} return rest_ensure_response($o); })); });
