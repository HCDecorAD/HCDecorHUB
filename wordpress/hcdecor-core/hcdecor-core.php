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


/* HCDECOR_MASTER_AUTO_V1: demo data + HUB workflow */
add_action('init',function(){
  register_post_type('hc_lead',[
    'labels'=>['name'=>'Khách hàng / Lead','singular_name'=>'Lead','add_new_item'=>'Thêm Lead'],
    'public'=>false,'show_ui'=>true,'menu_icon'=>'dashicons-groups',
    'supports'=>['title','editor','custom-fields']
  ]);
},12);

function hcdecor_master_seed_demo(){
  if(get_option('hcdecor_master_demo_v1')==='done') return;
  $projects=[
    ['Demo · Bảng hiệu showroom','Concept nhận diện mặt dựng và bảng hiệu.','Bảng hiệu'],
    ['Demo · Không gian nội thất','Concept nội thất kinh doanh hiện đại.','Nội thất'],
    ['Demo · Phối cảnh kiến trúc','Phối cảnh 3D và giải pháp kiến trúc.','3D & Phối cảnh']
  ];
  foreach($projects as $x){
    if(!get_page_by_title($x[0],OBJECT,'hc_project')){
      $id=wp_insert_post(['post_type'=>'hc_project','post_status'=>'publish','post_title'=>$x[0],'post_excerpt'=>$x[1],'post_content'=>$x[1]]);
      if($id && !is_wp_error($id)) update_post_meta($id,'hc_demo_category',$x[2]);
    }
  }
  if(!get_page_by_title('Demo Lead · Khách hàng mẫu',OBJECT,'hc_lead')){
    $id=wp_insert_post(['post_type'=>'hc_lead','post_status'=>'publish','post_title'=>'Demo Lead · Khách hàng mẫu','post_content'=>'Nhu cầu: tư vấn bảng hiệu / nội thất']);
    if($id&&!is_wp_error($id)){update_post_meta($id,'hc_status','new');update_post_meta($id,'hc_source','website-demo');}
  }
  if(post_type_exists('hc_quote') && !get_page_by_title('Demo Báo giá · Q-001',OBJECT,'hc_quote')){
    $id=wp_insert_post(['post_type'=>'hc_quote','post_status'=>'publish','post_title'=>'Demo Báo giá · Q-001','post_content'=>'Hạng mục demo · trạng thái Draft']);
    if($id&&!is_wp_error($id)) update_post_meta($id,'hc_status','draft');
  }
  update_option('hcdecor_master_demo_v1','done');
}
add_action('init','hcdecor_master_seed_demo',60);


/* HCDECOR_WORKFLOW_V2 */
add_action('init',function(){
  register_post_status('hc_new',['label'=>'Mới','public'=>false,'internal'=>true,'show_in_admin_all_list'=>true]);
  register_post_status('hc_contacting',['label'=>'Đang tư vấn','public'=>false,'internal'=>true,'show_in_admin_all_list'=>true]);
  register_post_status('hc_survey',['label'=>'Khảo sát','public'=>false,'internal'=>true,'show_in_admin_all_list'=>true]);
  register_post_status('hc_quoted',['label'=>'Đã báo giá','public'=>false,'internal'=>true,'show_in_admin_all_list'=>true]);
  register_post_status('hc_won',['label'=>'Đã chốt','public'=>false,'internal'=>true,'show_in_admin_all_list'=>true]);
},20);

function hcdecor_money($n){return number_format((float)$n,0,',','.').' đ';}
function hcdecor_quote_total($id){
  $qty=max(1,(float)get_post_meta($id,'hc_qty',true));
  $price=(float)get_post_meta($id,'hc_unit_price',true);
  $discount=(float)get_post_meta($id,'hc_discount',true);
  $vat=(float)get_post_meta($id,'hc_vat',true);
  $sub=$qty*$price; $after=max(0,$sub-$discount); return $after+($after*$vat/100);
}
add_action('add_meta_boxes',function(){
  add_meta_box('hc_lead_flow','HCDecor · Lead Workflow',function($p){
    $status=get_post_meta($p->ID,'hc_status',true)?:'new'; $phone=get_post_meta($p->ID,'hc_phone',true); $service=get_post_meta($p->ID,'hc_service',true);
    echo '<p>Điện thoại<br><input style="width:100%" name="hc_phone" value="'.esc_attr($phone).'"></p><p>Dịch vụ<br><input style="width:100%" name="hc_service" value="'.esc_attr($service).'"></p><p>Trạng thái<br><select name="hc_status">';
    foreach(['new'=>'Mới','contacting'=>'Đang tư vấn','survey'=>'Khảo sát','quoted'=>'Đã báo giá','won'=>'Đã chốt','lost'=>'Không chốt'] as $k=>$v) echo '<option value="'.$k.'" '.selected($status,$k,false).'>'.$v.'</option>';
    echo '</select></p>';
  },'hc_lead','normal','high');
  add_meta_box('hc_quote_calc','HCDecor · Chi tiết báo giá',function($p){
    foreach(['hc_customer'=>'Khách hàng','hc_item'=>'Hạng mục','hc_qty'=>'Số lượng','hc_unit_price'=>'Đơn giá','hc_discount'=>'Giảm giá','hc_vat'=>'VAT %'] as $k=>$v) echo '<p>'.$v.'<br><input style="width:100%" name="'.$k.'" value="'.esc_attr(get_post_meta($p->ID,$k,true)).'"></p>';
    echo '<p><strong>Tổng hiện tại: '.esc_html(hcdecor_money(hcdecor_quote_total($p->ID))).'</strong></p>';
  },'hc_quote','normal','high');
});
add_action('save_post',function($id){
  if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE) return;
  if(get_post_type($id)==='hc_lead') foreach(['hc_phone','hc_service','hc_status'] as $k) if(isset($_POST[$k])) update_post_meta($id,$k,sanitize_text_field(wp_unslash($_POST[$k])));
  if(get_post_type($id)==='hc_quote') foreach(['hc_customer','hc_item','hc_qty','hc_unit_price','hc_discount','hc_vat'] as $k) if(isset($_POST[$k])) update_post_meta($id,$k,sanitize_text_field(wp_unslash($_POST[$k])));
});

add_action('admin_menu',function(){
  add_submenu_page('hcdecor-hub','Pipeline','Pipeline','edit_posts','hcdecor-pipeline','hcdecor_pipeline_admin');
},20);
function hcdecor_pipeline_admin(){
  $statuses=['new'=>'Mới','contacting'=>'Đang tư vấn','survey'=>'Khảo sát','quoted'=>'Đã báo giá','won'=>'Đã chốt'];
  echo '<div class="wrap"><h1>HCDecor HUB · Pipeline</h1><div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px">';
  foreach($statuses as $k=>$label){$q=new WP_Query(['post_type'=>'hc_lead','post_status'=>'publish','meta_key'=>'hc_status','meta_value'=>$k,'posts_per_page'=>20]);echo '<section style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:14px"><h2>'.$label.' · '.$q->found_posts.'</h2>';foreach($q->posts as $p)echo '<p><a href="'.esc_url(get_edit_post_link($p->ID)).'">'.esc_html($p->post_title).'</a></p>';echo '</section>';}
  echo '</div></div>';
}


/* HCDECOR_WORKFLOW_V3: lead -> quote, multi-items, project gallery */
function hcdecor_quote_items($id){
  $v=get_post_meta($id,'hc_quote_items',true);
  return is_array($v)?$v:[];
}
function hcdecor_quote_items_total($id){
  $sum=0; foreach(hcdecor_quote_items($id) as $x) $sum+=((float)($x['qty']??0))*((float)($x['price']??0));
  $discount=(float)get_post_meta($id,'hc_discount',true); $vat=(float)get_post_meta($id,'hc_vat',true);
  $after=max(0,$sum-$discount); return ['subtotal'=>$sum,'total'=>$after+($after*$vat/100)];
}
add_action('add_meta_boxes',function(){
  add_meta_box('hc_project_gallery','HCDecor · Gallery dự án',function($p){
    $ids=(array)get_post_meta($p->ID,'hc_gallery_ids',true);
    echo '<p>Media IDs (cách nhau bằng dấu phẩy)</p><input style="width:100%" name="hc_gallery_ids" value="'.esc_attr(implode(',',$ids)).'"><p><a class="button" href="'.esc_url(admin_url('media-new.php')).'">Upload hình ảnh</a></p>';
    if($ids){echo '<div style="display:flex;gap:8px;flex-wrap:wrap">';foreach($ids as $aid){$src=wp_get_attachment_image_url((int)$aid,'thumbnail');if($src)echo '<img src="'.esc_url($src).'" style="width:100px;height:80px;object-fit:cover;border-radius:6px">';}echo '</div>';}
  },'hc_project','normal','high');
  add_meta_box('hc_quote_items','HCDecor · Hạng mục báo giá',function($p){
    $items=hcdecor_quote_items($p->ID); for($i=0;$i<8;$i++){ $x=$items[$i]??[]; echo '<p><input name="hc_item_name[]" placeholder="Hạng mục" value="'.esc_attr($x['name']??'').'" style="width:45%"> <input name="hc_item_qty[]" placeholder="SL" value="'.esc_attr($x['qty']??'').'" style="width:10%"> <input name="hc_item_price[]" placeholder="Đơn giá" value="'.esc_attr($x['price']??'').'" style="width:25%"></p>'; }
    $t=hcdecor_quote_items_total($p->ID); echo '<p><strong>Tạm tính: '.esc_html(hcdecor_money($t['subtotal'])).' · Tổng: '.esc_html(hcdecor_money($t['total'])).'</strong></p>';
  },'hc_quote','normal','high');
},20);
add_action('save_post',function($id){
  if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE) return;
  if(get_post_type($id)==='hc_project' && isset($_POST['hc_gallery_ids'])){
    $ids=array_values(array_filter(array_map('intval',explode(',',sanitize_text_field(wp_unslash($_POST['hc_gallery_ids']))))));
    update_post_meta($id,'hc_gallery_ids',$ids);
  }
  if(get_post_type($id)==='hc_quote' && isset($_POST['hc_item_name'])){
    $items=[]; $names=(array)$_POST['hc_item_name']; $qty=(array)($_POST['hc_item_qty']??[]); $prices=(array)($_POST['hc_item_price']??[]);
    foreach($names as $i=>$n){$n=sanitize_text_field(wp_unslash($n));if($n!=='')$items[]=['name'=>$n,'qty'=>(float)($qty[$i]??0),'price'=>(float)($prices[$i]??0)];}
    update_post_meta($id,'hc_quote_items',$items);
  }
},20);

add_filter('post_row_actions',function($a,$p){
  if($p->post_type==='hc_lead') $a['hc_quote']='<a href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=hcdecor_lead_to_quote&lead='.$p->ID),'hcdecor_lead_to_quote_'.$p->ID)).'">Tạo báo giá</a>';
  if($p->post_type==='hc_quote') $a['hc_print']='<a target="_blank" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=hcdecor_print_quote&quote='.$p->ID),'hcdecor_print_quote_'.$p->ID)).'">Xem/In báo giá</a>';
  return $a;
},10,2);
add_action('admin_post_hcdecor_lead_to_quote',function(){
  $lead=(int)($_GET['lead']??0); check_admin_referer('hcdecor_lead_to_quote_'.$lead); if(!current_user_can('edit_post',$lead)) wp_die('Forbidden');
  $p=get_post($lead); $qid=wp_insert_post(['post_type'=>'hc_quote','post_status'=>'publish','post_title'=>'Báo giá · '.$p->post_title]);
  if($qid&&!is_wp_error($qid)){update_post_meta($qid,'hc_customer',$p->post_title);update_post_meta($qid,'hc_phone',get_post_meta($lead,'hc_phone',true));update_post_meta($lead,'hc_status','quoted');wp_safe_redirect(get_edit_post_link($qid,'raw'));exit;}
  wp_die('Cannot create quote');
});
add_action('admin_post_hcdecor_print_quote',function(){
  $id=(int)($_GET['quote']??0); check_admin_referer('hcdecor_print_quote_'.$id); if(!current_user_can('edit_post',$id)) wp_die('Forbidden');
  $q=get_post($id); $t=hcdecor_quote_items_total($id); echo '<!doctype html><meta charset="utf-8"><title>'.esc_html($q->post_title).'</title><style>body{font-family:Arial;max-width:900px;margin:40px auto;color:#171717}h1{border-bottom:3px solid #b77b36;padding-bottom:14px}table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #ddd;text-align:left}.num{text-align:right}@media print{button{display:none}}</style><h1>HCDecor HUB · BÁO GIÁ</h1><p><strong>'.esc_html(get_post_meta($id,'hc_customer',true)).'</strong> · '.esc_html(get_post_meta($id,'hc_phone',true)).'</p><table><tr><th>Hạng mục</th><th>SL</th><th class="num">Đơn giá</th><th class="num">Thành tiền</th></tr>';
  foreach(hcdecor_quote_items($id) as $x){$line=(float)$x['qty']*(float)$x['price'];echo '<tr><td>'.esc_html($x['name']).'</td><td>'.esc_html($x['qty']).'</td><td class="num">'.esc_html(hcdecor_money($x['price'])).'</td><td class="num">'.esc_html(hcdecor_money($line)).'</td></tr>';}
  echo '</table><h2 style="text-align:right">Tổng: '.esc_html(hcdecor_money($t['total'])).'</h2><p>HCDecor · 231D An Dương Vương, P. An Lạc, Tp.HCM · 0888 821 842</p><button onclick="print()">In / Lưu PDF</button>'; exit;
});
