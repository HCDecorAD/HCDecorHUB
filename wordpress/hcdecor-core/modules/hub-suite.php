<?php
if (!defined('ABSPATH')) exit;
function hcdecor_suite_sites(){ $v=get_option('hcdecor_suite_sites',array()); return is_array($v)?array_slice($v,-100,null,true):array(); }
function hcdecor_suite_presets(){ $v=get_option('hcdecor_suite_presets',array()); return is_array($v)?array_slice($v,-100,null,true):array(); }
function hcdecor_suite_stats(){ $ids=get_posts(array('post_type'=>'hc_automation_task','post_status'=>'publish','numberposts'=>500,'fields'=>'ids')); $o=array('total'=>count($ids),'done'=>0,'failed'=>0,'running'=>0,'queued'=>0); foreach($ids as $id){$raw=get_post_meta($id,'hc_auto_status',true);$s=is_scalar($raw)?sanitize_key((string)$raw):'';if(isset($o[$s]))$o[$s]++;} return $o; }
add_action('admin_menu',function(){ add_submenu_page('hcdecor-hub','HUB Control Center','Control Center','edit_posts','hcdecor-suite','hcdecor_suite_page',3); },30);
function hcdecor_suite_page(){ if(!current_user_can('edit_posts')) return; $sites=hcdecor_suite_sites(); $presets=hcdecor_suite_presets(); $stats=hcdecor_suite_stats(); echo '<div class="wrap"><h1>HCDecor HUB - Control Center</h1><p>Websites: '.count($sites).' | Presets: '.count($presets).' | Tasks: '.(int)$stats['total'].'</p></div>'; }
add_action('rest_api_init',function(){ register_rest_route('hcdecor/v1','/suite/status',array('methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth','callback'=>function(){return rest_ensure_response(array('sites'=>count(hcdecor_suite_sites()),'presets'=>count(hcdecor_suite_presets()),'automation'=>hcdecor_suite_stats()));})); });
