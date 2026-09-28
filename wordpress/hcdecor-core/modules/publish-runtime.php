<?php
if (!defined('ABSPATH')) exit;
function hcdecor_runtime_jobs(){ $v=get_option('hcdecor_runtime_jobs',array()); return is_array($v)?$v:array(); }
function hcdecor_runtime_prune($jobs,$limit=100){ if(!is_array($jobs)) return array(); if(count($jobs)<=$limit) return $jobs; uasort($jobs,function($a,$b){ return strcmp((string)($b['created']??''),(string)($a['created']??'')); }); return array_slice($jobs,0,$limit,true); }
function hcdecor_runtime_safe_jobs(){ $out=array(); foreach(hcdecor_runtime_jobs() as $id=>$j){ $out[$id]=array('type'=>(string)($j['type']??''),'target'=>(string)($j['target']??''),'status'=>(string)($j['status']??''),'attempts'=>(int)($j['attempts']??0),'scheduled'=>(string)($j['scheduled']??''),'created'=>(string)($j['created']??''),'last_error'=>(string)($j['last_error']??''),'production_approved'=>!empty($j['production_approved']),'production_approved_by'=>(int)($j['production_approved_by']??0),'production_approved_at'=>(string)($j['production_approved_at']??'')); } return $out; }
function hcdecor_runtime_enqueue($type,$target,$payload=array(),$when='',$production_approved=false){
    $v=hcdecor_runtime_jobs(); $id='job_'.wp_generate_password(10,false,false);
    $approved=(bool)$production_approved && get_current_user_id()>0 && current_user_can('manage_options');
    $v[$id]=array('type'=>sanitize_key($type),'target'=>sanitize_key($target),'payload'=>$payload,'status'=>'queued','attempts'=>0,'scheduled'=>$when?$when:current_time('mysql'),'created'=>current_time('mysql'),'last_error'=>'','result'=>array(),'production_approved'=>$approved,'production_approved_by'=>$approved?get_current_user_id():0,'production_approved_at'=>$approved?current_time('mysql'):'');
    $v=hcdecor_runtime_prune($v); update_option('hcdecor_runtime_jobs',$v,false); if(!wp_next_scheduled('hcdecor_runtime_tick')) wp_schedule_single_event(time()+5,'hcdecor_runtime_tick'); return $id;
}
function hcdecor_runtime_approval_fresh($job){
    if(empty($job['production_approved']) || empty($job['production_approved_by']) || empty($job['production_approved_at'])) return false;
    $at=strtotime((string)$job['production_approved_at'])?:0;
    return $at>0 && $at>=time()-15*MINUTE_IN_SECONDS;
}
function hcdecor_runtime_execute($id,$job){ if(!hcdecor_runtime_approval_fresh($job)) return new WP_Error('approval','Fresh production approval required'); $conns=function_exists('hcdecor_conn_get')?hcdecor_conn_get():array(); $conn=isset($conns[$job['target']])?$conns[$job['target']]:null; if(!$conn||empty($conn['enabled'])||empty($conn['endpoint'])) return new WP_Error('connection','Connection unavailable'); if(!function_exists('hcdecor_conn_public_https')||!hcdecor_conn_public_https($conn['endpoint'])) return new WP_Error('connection_url','Unsafe connection endpoint'); $args=array('timeout'=>20,'redirection'=>0,'headers'=>array('Content-Type'=>'application/json'),'body'=>wp_json_encode($job['payload'])); if(!empty($conn['secret'])) $args['headers']['Authorization']='Bearer '.$conn['secret']; $res=wp_safe_remote_post($conn['endpoint'],$args); if(is_wp_error($res)) return $res; $code=wp_remote_retrieve_response_code($res); if($code<200||$code>=300) return new WP_Error('remote_http','HTTP '.$code); return array('http'=>$code); }
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
  if(is_wp_error($res)){ $v[$id]['last_error']=$res->get_error_message(); $v[$id]['status']='failed'; }
  else { $v[$id]['status']='done'; $v[$id]['result']=$res; }
  $v[$id]['payload']=array(); $changed=true;
 }
 if($changed) update_option('hcdecor_runtime_jobs',hcdecor_runtime_prune($v),false);
});
add_action('admin_menu',function(){ add_submenu_page('hcdecor-hub','Publish Runtime','Publish Runtime','edit_posts','hcdecor-runtime','hcdecor_runtime_page',6); },30);
function hcdecor_runtime_page(){ if(!current_user_can('edit_posts')) return; $jobs=hcdecor_runtime_jobs(); echo '<div class="wrap"><h1>HCDecor Publish Runtime</h1><p>Queued jobs: '.count($jobs).'</p></div>'; }
add_action('rest_api_init',function(){ register_rest_route('hcdecor/v1','/runtime/jobs',array('methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth','callback'=>function(){return rest_ensure_response(hcdecor_runtime_safe_jobs());})); });
