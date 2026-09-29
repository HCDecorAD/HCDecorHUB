<?php
if (!defined('ABSPATH')) exit;
function hcdecor_runtime_prune($jobs,$limit=100){ if(!is_array($jobs)) return array(); if(count($jobs)<=$limit) return $jobs; uasort($jobs,function($a,$b){ return strcmp((string)($b['created']??''),(string)($a['created']??'')); }); return array_slice($jobs,0,$limit,true); }
function hcdecor_runtime_jobs(){ $v=get_option('hcdecor_runtime_jobs',array()); return hcdecor_runtime_prune(is_array($v)?$v:array()); }
function hcdecor_runtime_clip($value,$limit=500){ $value=(string)$value; return function_exists('mb_substr')?mb_substr($value,0,$limit):substr($value,0,$limit); }
function hcdecor_runtime_safe_jobs(){ $out=array(); foreach(hcdecor_runtime_jobs() as $id=>$j){ $safe_id=hcdecor_runtime_clip(sanitize_key((string)$id),100); $out[$safe_id]=array('type'=>hcdecor_runtime_clip(sanitize_key((string)($j['type']??'')),100),'target'=>hcdecor_runtime_clip(sanitize_key((string)($j['target']??'')),100),'status'=>hcdecor_runtime_clip(sanitize_key((string)($j['status']??'')),50),'attempts'=>max(0,min(100,(int)($j['attempts']??0))),'scheduled'=>hcdecor_runtime_clip($j['scheduled']??'',64),'created'=>hcdecor_runtime_clip($j['created']??'',64),'last_error'=>hcdecor_runtime_clip($j['last_error']??'',500),'production_approved'=>!empty($j['production_approved']),'production_approved_by'=>(int)($j['production_approved_by']??0),'production_approved_at'=>hcdecor_runtime_clip($j['production_approved_at']??'',64),'production_approval_source'=>hcdecor_runtime_clip(sanitize_key((string)($j['production_approval_source']??'')),50)); } return $out; }
function hcdecor_runtime_approval_fresh($job){
    if(empty($job['production_approved']) || empty($job['production_approved_by']) || empty($job['production_approved_at'])) return false;
    $by=is_scalar($job['production_approved_by'])?(string)$job['production_approved_by']:'';
    $source=sanitize_key(is_scalar($job['production_approval_source']??'')?(string)$job['production_approval_source']:'');
    $at_raw=is_scalar($job['production_approved_at'])?(string)$job['production_approved_at']:'';
    if($by==='' || strlen($by)>200 || strlen($source)>50 || strlen($at_raw)>64 || !in_array($source,['wp_user','service_bridge'],true)) return false;
    $at=strtotime($at_raw)?:0;
    return $at>0 && $at>=time()-15*MINUTE_IN_SECONDS && $at<=time()+5*MINUTE_IN_SECONDS;
}
function hcdecor_runtime_execute($id,$job){ if(!hcdecor_runtime_approval_fresh($job)) return new WP_Error('approval','Fresh production approval required'); $conns=function_exists('hcdecor_conn_get')?hcdecor_conn_get():array(); $conn=isset($conns[$job['target']])?$conns[$job['target']]:null; if(!$conn||empty($conn['enabled'])||empty($conn['endpoint'])) return new WP_Error('connection','Connection unavailable'); if(!function_exists('hcdecor_conn_public_https')||!hcdecor_conn_public_https($conn['endpoint'])) return new WP_Error('connection_url','Unsafe connection endpoint'); $body=wp_json_encode($job['payload'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); if(!is_string($body)||strlen($body)>256*1024) return new WP_Error('payload_size','Runtime payload exceeds 256 KB.'); $args=array('timeout'=>20,'redirection'=>0,'limit_response_size'=>64*1024,'headers'=>array('Content-Type'=>'application/json'),'body'=>$body); if(!empty($conn['secret'])) $args['headers']['Authorization']='Bearer '.$conn['secret']; $res=wp_safe_remote_post($conn['endpoint'],$args); if(is_wp_error($res)) return $res; $code=wp_remote_retrieve_response_code($res); if($code<200||$code>=300) return new WP_Error('remote_http','HTTP '.$code); return array('http'=>$code); }
add_action('hcdecor_runtime_tick',function(){
 $v=hcdecor_runtime_jobs(); $changed=false; $now=current_time('timestamp');
 foreach($v as $id=>$j){
  if(($j['status']??'')!=='queued'||strtotime((string)($j['scheduled']??''))>$now) continue;
  if(!hcdecor_runtime_approval_fresh($j)){ $v[$id]['status']='failed'; $v[$id]['last_error']='Fresh production approval required.'; $v[$id]['payload']=array(); $v[$id]['production_approved']=false; $changed=true; continue; }
  $approved_job=$j;
  $v[$id]['status']='running'; $v[$id]['attempts']=(int)($v[$id]['attempts']??0)+1;
  $v[$id]['production_approved']=false; $v[$id]['approval_consumed_at']=current_time('mysql');
  update_option('hcdecor_runtime_jobs',$v,false);
  $res=hcdecor_runtime_execute($id,$approved_job);
  if(is_wp_error($res)){ $msg=function_exists('hcdecor_conn_safe_error')?hcdecor_conn_safe_error($res->get_error_message()):'Runtime delivery failed.'; $v[$id]['last_error']=function_exists('mb_substr')?mb_substr($msg,0,500):substr($msg,0,500); $v[$id]['status']='failed'; }
  else { $v[$id]['status']='done'; $v[$id]['result']=$res; }
  $v[$id]['payload']=array(); $changed=true;
 }
 if($changed) update_option('hcdecor_runtime_jobs',hcdecor_runtime_prune($v),false);
});
add_action('admin_menu',function(){ add_submenu_page('hcdecor-hub','Publish Runtime','Publish Runtime','manage_options','hcdecor-runtime','hcdecor_runtime_page',6); },30);
function hcdecor_runtime_page(){ if(!current_user_can('manage_options')) return; $jobs=hcdecor_runtime_jobs(); echo '<div class="wrap"><h1>HCDecor Publish Runtime</h1><p>Queued jobs: '.count($jobs).'</p></div>'; }
add_action('rest_api_init',function(){ register_rest_route('hcdecor/v1','/runtime/jobs',array('methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth','callback'=>function(){return rest_ensure_response(hcdecor_runtime_safe_jobs());})); });
