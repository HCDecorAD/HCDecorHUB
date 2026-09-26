<?php
/**
 * Plugin Name: HCDecor Core
 * Description: Data/API foundation and bootstrap for HCDecor HUB + Elementor.
 * Version: 0.2.0
 * Author: HCDecor
 */
if (!defined('ABSPATH')) exit;

function hcdecor_register_content() {
  register_post_type('hc_project', [
    'labels'=>['name'=>'Dự án','singular_name'=>'Dự án','add_new_item'=>'Thêm dự án'],
    'public'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-portfolio',
    'supports'=>['title','editor','thumbnail','excerpt','revisions'],
    'rewrite'=>['slug'=>'du-an'],'has_archive'=>true
  ]);
  register_taxonomy('hc_project_type','hc_project',[
    'labels'=>['name'=>'Loại dự án','singular_name'=>'Loại dự án'],
    'public'=>true,'show_in_rest'=>true,'hierarchical'=>true,
    'rewrite'=>['slug'=>'loai-du-an']
  ]);
  register_post_type('hc_service', [
    'labels'=>['name'=>'Dịch vụ','singular_name'=>'Dịch vụ','add_new_item'=>'Thêm dịch vụ'],
    'public'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-hammer',
    'supports'=>['title','editor','thumbnail','excerpt','revisions'],
    'rewrite'=>['slug'=>'dich-vu'],'has_archive'=>true
  ]);
}
add_action('init','hcdecor_register_content');

function hcdecor_ensure_page($title,$slug) {
  $page=get_page_by_path($slug);
  if ($page) return $page->ID;
  return wp_insert_post(['post_title'=>$title,'post_name'=>$slug,'post_status'=>'publish','post_type'=>'page']);
}
function hcdecor_activate() {
  hcdecor_register_content();
  $home=hcdecor_ensure_page('Trang chủ','trang-chu');
  hcdecor_ensure_page('Giới thiệu','gioi-thieu');
  hcdecor_ensure_page('Dịch vụ','dich-vu-hcdecor');
  hcdecor_ensure_page('Dự án','du-an-hcdecor');
  hcdecor_ensure_page('Liên hệ','lien-he');
  update_option('show_on_front','page');
  update_option('page_on_front',$home);
  foreach(['Bảng hiệu','Nội thất','3D & Phối cảnh','Kiến trúc'] as $name) {
    if (!term_exists($name,'hc_project_type')) wp_insert_term($name,'hc_project_type');
  }
  flush_rewrite_rules();
}
register_activation_hook(__FILE__,'hcdecor_activate');
register_deactivation_hook(__FILE__,'flush_rewrite_rules');

add_action('rest_api_init', function () {
  register_rest_route('hcdecor/v1','/site',[
    'methods'=>'GET','permission_callback'=>'__return_true',
    'callback'=>function(){
      return rest_ensure_response([
        'name'=>'HCDecor HUB','phone'=>'0888 821 842',
        'address'=>'231D An Dương Vương, P. An Lạc, Tp.HCM, Việt Nam',
        'social'=>[
          'facebook'=>'https://www.facebook.com/hocuong1979/',
          'tiktok'=>'https://www.tiktok.com/@quangcaohocuong',
          'youtube'=>'https://www.youtube.com/@HoCuongPre',
          'zalo'=>'https://zalo.me/quangcaohocuong'
        ]
      ]);
    }
  ]);
});

add_action('admin_notices',function(){
  if (!current_user_can('manage_options')) return;
  echo '<div class="notice notice-success"><p><strong>HCDecor Core:</strong> dữ liệu nền đã sẵn sàng. Cài/activate Hello Elementor + Elementor, sau đó mở Trang chủ bằng Elementor.</p></div>';
});

add_action('wp_enqueue_scripts',function(){wp_enqueue_style('hcdecor-elementor',plugins_url('assets/hcdecor-elementor.css',__FILE__),[], '0.2.0');});
