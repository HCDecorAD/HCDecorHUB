<?php
if (!defined('ABSPATH')) exit;
function hcdecor_full_calendar_items(){ $ids=get_posts(array('post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>200,'fields'=>'ids','orderby'=>'date','order'=>'DESC')); $out=array(); foreach($ids as $id){$out[]=array('id'=>$id,'title'=>get_the_title($id),'status'=>(string)get_post_meta($id,'hc_auto_status',true),'type'=>(string)get_post_meta($id,'hc_auto_type',true),'date'=>get_post_field('post_date',$id));} return $out; }
function hcdecor_full_audit_sanitize($value,$key='',$depth=0){
    $key=strtolower((string)$key);
    if(preg_match('/(?:token|secret|password|authorization|api[_-]?key|cookie|refresh|credential)/',$key)) return '[REDACTED]';
    if($depth>=5) return '[DEPTH_LIMIT]';
    if(is_array($value)){ $out=array(); foreach(array_slice($value,0,30,true) as $k=>$v) $out[$k]=hcdecor_full_audit_sanitize($v,(string)$k,$depth+1); return $out; }
    if(is_object($value)) return '[OBJECT]';
    if(is_bool($value)||is_int($value)||is_float($value)||$value===null) return $value;
    $text=sanitize_text_field((string)$value);
    if(strlen($text)>500) $text=substr($text,0,500).'…';
    return $text;
}
function hcdecor_full_audit_entries(){ $v=get_option('hcdecor_audit_log',array()); return is_array($v)?array_slice($v,-300):array(); }
function hcdecor_full_audit($event,$data=array()){ $v=hcdecor_full_audit_entries(); $v[]=array('at'=>current_time('mysql'),'user'=>get_current_user_id(),'event'=>sanitize_key($event),'data'=>hcdecor_full_audit_sanitize((array)$data)); if(count($v)>300)$v=array_slice($v,-300); update_option('hcdecor_audit_log',$v,false); }
add_action('admin_menu',function(){ add_submenu_page('hcdecor-hub','HUB Full Operations','Full Operations','edit_posts','hcdecor-full','hcdecor_full_page',8); },32);
function hcdecor_full_page(){ if(!current_user_can('edit_posts')) return; $cal=hcdecor_full_calendar_items(); $audit=hcdecor_full_audit_entries(); echo '<div class="wrap"><h1>HCDecor HUB - Full Operations</h1><p>Calendar items: '.count($cal).' | Audit entries: '.count($audit).'</p></div>'; }
add_action('rest_api_init',function(){ register_rest_route('hcdecor/v1','/full/status',array('methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth','callback'=>function(){return rest_ensure_response(array('calendar'=>count(hcdecor_full_calendar_items()),'audit'=>count(hcdecor_full_audit_entries())));})); });
