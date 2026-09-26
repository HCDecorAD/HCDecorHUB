<?php
/**
 * Plugin Name: HCDecor Core
 * Description: Data/API foundation and bootstrap for HCDecor HUB + Elementor.
 * Version: 0.5.0
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

add_action('wp_enqueue_scripts',function(){wp_enqueue_style('hcdecor-elementor',plugins_url('assets/hcdecor-elementor.css',__FILE__),[], '0.3.0'); wp_enqueue_style('hcdecor-homepage',plugins_url('assets/hcdecor-homepage.css',__FILE__),['hcdecor-elementor'], '0.3.0');});

function hcdecor_site_data(){
  return [
    'brand'=>['name'=>'HCDecor HUB','tagline'=>'Thiết kế · Thi công · Nội thất · Kiến trúc · 3D'],
    'contact'=>['phone'=>'0888 821 842','tel'=>'+84888821842','address'=>'231D An Dương Vương, P. An Lạc, Tp.HCM, Việt Nam'],
    'social'=>[
      'facebook'=>'https://www.facebook.com/hocuong1979/',
      'tiktok'=>'https://www.tiktok.com/@quangcaohocuong',
      'youtube'=>'https://www.youtube.com/@HoCuongPre',
      'zalo'=>'https://zalo.me/quangcaohocuong'
    ]
  ];
}

add_action('rest_api_init',function(){
  register_rest_route('hcdecor/v1','/content',[
    'methods'=>'GET','permission_callback'=>'__return_true',
    'callback'=>function(){
      $services=get_posts(['post_type'=>'hc_service','post_status'=>'publish','numberposts'=>20,'orderby'=>'menu_order title','order'=>'ASC']);
      $projects=get_posts(['post_type'=>'hc_project','post_status'=>'publish','numberposts'=>12,'orderby'=>'date','order'=>'DESC']);
      $map=function($p){return ['id'=>$p->ID,'title'=>get_the_title($p),'excerpt'=>get_the_excerpt($p),'image'=>get_the_post_thumbnail_url($p,'large')?:'','url'=>get_permalink($p)];};
      return rest_ensure_response(['site'=>hcdecor_site_data(),'services'=>array_map($map,$services),'projects'=>array_map($map,$projects)]);
    }
  ]);
});

function hcdecor_seed_services(){
  $items=[
    ['Bảng hiệu','Thiết kế và thi công bảng hiệu, mặt dựng và nhận diện không gian.'],
    ['Nội thất','Thiết kế và triển khai nội thất theo nhu cầu sử dụng thực tế.'],
    ['3D & Phối cảnh','Phối cảnh 3D giúp hình dung phương án trước khi triển khai.'],
    ['Kiến trúc','Giải pháp kiến trúc cân bằng thẩm mỹ, công năng và khả năng thi công.']
  ];
  foreach($items as $item){
    if(!get_page_by_title($item[0],OBJECT,'hc_service')){
      wp_insert_post(['post_type'=>'hc_service','post_status'=>'publish','post_title'=>$item[0],'post_excerpt'=>$item[1],'post_content'=>$item[1]]);
    }
  }
}
add_action('init',function(){if(get_option('hcdecor_seed_v1')!=='done'){hcdecor_seed_services();update_option('hcdecor_seed_v1','done');}},20);

add_action('after_setup_theme',function(){
  add_theme_support('post-thumbnails');
  add_theme_support('title-tag');
  add_theme_support('custom-logo');
});

add_action('wp_head',function(){
  echo '<meta name="theme-color" content="#090b0d">';
},1);

add_filter('show_admin_bar',function($show){return is_admin()?$show:false;});


/* Phase 2: ACF data model + lead integration foundation */
add_action('acf/init',function(){
  if(!function_exists('acf_add_local_field_group')) return;
  acf_add_local_field_group([
    'key'=>'group_hc_project','title'=>'HCDecor · Dữ liệu dự án',
    'fields'=>[
      ['key'=>'field_hc_client','label'=>'Khách hàng','name'=>'hc_client','type'=>'text'],
      ['key'=>'field_hc_location','label'=>'Địa điểm','name'=>'hc_location','type'=>'text'],
      ['key'=>'field_hc_year','label'=>'Năm','name'=>'hc_year','type'=>'number'],
      ['key'=>'field_hc_summary','label'=>'Tóm tắt','name'=>'hc_summary','type'=>'textarea'],
      ['key'=>'field_hc_gallery','label'=>'Thư viện ảnh','name'=>'hc_gallery','type'=>'gallery','return_format'=>'id']
    ],
    'location'=>[[['param'=>'post_type','operator'=>'==','value'=>'hc_project']]]
  ]);
  acf_add_local_field_group([
    'key'=>'group_hc_service','title'=>'HCDecor · Dữ liệu dịch vụ',
    'fields'=>[
      ['key'=>'field_hc_service_icon','label'=>'Icon/nhãn','name'=>'hc_service_icon','type'=>'text'],
      ['key'=>'field_hc_service_summary','label'=>'Mô tả ngắn','name'=>'hc_service_summary','type'=>'textarea'],
      ['key'=>'field_hc_service_order','label'=>'Thứ tự','name'=>'hc_service_order','type'=>'number','default_value'=>10]
    ],
    'location'=>[[['param'=>'post_type','operator'=>'==','value'=>'hc_service']]]
  ]);
});

add_action('rest_api_init',function(){
  register_rest_route('hcdecor/v1','/phase2',[
    'methods'=>'GET','permission_callback'=>'__return_true',
    'callback'=>function(){
      return rest_ensure_response([
        'acf'=>function_exists('acf_add_local_field_group'),
        'fluentform'=>defined('FLUENTFORM'),
        'webhooks'=>class_exists('WP_Webhooks_Pro')||defined('WPWH_VERSION')||is_plugin_active('wp-webhooks/wp-webhooks.php'),
        'outbound_enabled'=>false
      ]);
    }
  ]);
});


/* Phase 2B: navigation, lead intake, safe automation hooks */
add_action('init',function(){
  register_nav_menus(['hcdecor_primary'=>'HCDecor Primary']);
  $menu=wp_get_nav_menu_object('HCDecor Primary');
  if(!$menu){
    $mid=wp_create_nav_menu('HCDecor Primary');
    foreach([['Trang chủ','trang-chu'],['Giới thiệu','gioi-thieu'],['Dịch vụ','dich-vu-hcdecor'],['Dự án','du-an-hcdecor'],['Liên hệ','lien-he']] as $i){
      $p=get_page_by_path($i[1]);
      if($p) wp_update_nav_menu_item($mid,0,['menu-item-title'=>$i[0],'menu-item-object'=>'page','menu-item-object-id'=>$p->ID,'menu-item-type'=>'post_type','menu-item-status'=>'publish']);
    }
  }
},30);

function hcdecor_lead_schema(){
  return ['name'=>'','phone'=>'','email'=>'','service'=>'','message'=>'','source'=>'website'];
}
add_action('rest_api_init',function(){
  register_rest_route('hcdecor/v1','/lead-schema',[
    'methods'=>'GET','permission_callback'=>'__return_true',
    'callback'=>function(){return rest_ensure_response(['fields'=>hcdecor_lead_schema(),'outbound'=>false,'provider'=>'Fluent Forms']);}
  ]);
});

add_action('wp_footer',function(){
  if(!is_front_page()) return;
  $s=hcdecor_site_data(); ?>
  <header class="hc-site-header">
    <a class="hc-brand" href="<?php echo esc_url(home_url('/')); ?>">HCDecor <span>HUB</span></a>
    <nav class="hc-nav" aria-label="HCDecor"><?php wp_nav_menu(['theme_location'=>'hcdecor_primary','container'=>false,'fallback_cb'=>false]); ?></nav>
    <div class="hc-header-actions"><a href="tel:<?php echo esc_attr($s['contact']['tel']); ?>">0888 821 842</a><a class="hc-lang" href="#" aria-label="Language">🇬🇧 EN</a></div>
  </header>
  <footer class="hc-site-footer">
    <div><strong>HCDecor HUB</strong><p><?php echo esc_html($s['brand']['tagline']); ?></p></div>
    <div><p><?php echo esc_html($s['contact']['address']); ?></p><a href="tel:<?php echo esc_attr($s['contact']['tel']); ?>">0888 821 842</a> · <a href="<?php echo esc_url($s['social']['zalo']); ?>">Zalo</a></div>
  </footer>
<?php },5);


/* Demo operations */
add_action('init',function(){
  register_post_type('hc_quote',[
    'labels'=>['name'=>'Báo giá','singular_name'=>'Báo giá','add_new_item'=>'Tạo báo giá'],
    'public'=>false,'show_ui'=>true,'menu_icon'=>'dashicons-media-spreadsheet',
    'supports'=>['title','editor','custom-fields']
  ]);
});
add_action('admin_menu',function(){
  add_menu_page('HCDecor HUB','HCDecor HUB','edit_posts','hcdecor-hub','hcdecor_hub_admin','dashicons-layout',3);
});
function hcdecor_hub_admin(){
  $projects=wp_count_posts('hc_project')->publish??0;
  $quotes=wp_count_posts('hc_quote')->publish??0;
  $media=wp_count_attachments()->inherit??0;
  echo '<div class="wrap"><h1>HCDecor HUB · Demo</h1><p>Website · Dự án · Media · Báo giá · Automation</p>';
  echo '<p><strong>Dự án:</strong> '.intval($projects).' &nbsp; <strong>Media:</strong> '.intval($media).' &nbsp; <strong>Báo giá:</strong> '.intval($quotes).'</p>';
  echo '<p><a class="button button-primary" href="'.esc_url(admin_url('post-new.php?post_type=hc_project')).'">+ Tạo dự án</a> <a class="button" href="'.esc_url(admin_url('upload.php')).'">Upload hình ảnh</a> <a class="button" href="'.esc_url(admin_url('post-new.php?post_type=hc_quote')).'">+ Tạo báo giá</a></p></div>';
}
