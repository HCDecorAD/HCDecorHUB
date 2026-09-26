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


/* HCDECOR_AGENT_DEMO_V1: content/media publishing workspace */
add_action('init',function(){
  register_post_type('hc_content_job',[
    'labels'=>['name'=>'AI Content Jobs','singular_name'=>'AI Content Job','add_new_item'=>'Tạo Content Job'],
    'public'=>false,'show_ui'=>true,'show_in_menu'=>false,'supports'=>['title','editor','custom-fields']
  ]);
},15);

add_action('admin_menu',function(){
  add_submenu_page('hcdecor-hub','AI Agent','AI Agent','edit_posts','hcdecor-agent','hcdecor_agent_admin',1);
  add_submenu_page('hcdecor-hub','Content Queue','Content Queue','edit_posts','edit.php?post_type=hc_content_job');
  add_submenu_page('hcdecor-hub','Media Library','Media Library','upload_files','upload.php');
  add_submenu_page('hcdecor-hub','Projects','Projects','edit_posts','edit.php?post_type=hc_project');
},15);

function hcdecor_agent_admin(){
  $jobs=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>8,'orderby'=>'date','order'=>'DESC']);
  $projects=get_posts(['post_type'=>'hc_project','post_status'=>'publish','numberposts'=>6]);
  echo '<div class="wrap"><h1>HCDecor HUB · AI Agent Demo</h1><p><strong>Project → Media → AI Content → Review → Publish</strong></p>';
  echo '<div style="display:grid;grid-template-columns:2fr 1fr;gap:18px;max-width:1200px">';
  echo '<section style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:12px"><h2>Agent Workspace</h2><form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="hcdecor_create_content_job">'.wp_nonce_field('hcdecor_agent_job','_wpnonce',true,false).'<p><label>Dự án</label><br><select name="project_id" style="width:100%;max-width:600px"><option value="0">Chọn dự án...</option>'; foreach($projects as $p) echo '<option value="'.$p->ID.'">'.esc_html($p->post_title).'</option>'; echo '</select></p><p><label>Yêu cầu cho AI Agent</label><br><textarea name="brief" rows="5" style="width:100%;max-width:800px" placeholder="Ví dụ: tạo nội dung giới thiệu dự án, caption Facebook/TikTok, đề xuất ảnh cover..."></textarea></p><p><label>Kênh đầu ra</label><br><label><input type="checkbox" name="channels[]" value="web" checked> Web</label> &nbsp; <label><input type="checkbox" name="channels[]" value="facebook"> Facebook</label> &nbsp; <label><input type="checkbox" name="channels[]" value="tiktok"> TikTok</label> &nbsp; <label><input type="checkbox" name="channels[]" value="youtube"> YouTube</label></p><button class="button button-primary">Tạo AI Content Job</button></form></section>';
  echo '<aside style="background:#111;color:#eee;padding:20px;border-radius:12px"><h2 style="color:#fff">Pipeline</h2><p>① Upload Media</p><p>② Gắn vào Project</p><p>③ AI xử lý Content</p><p>④ Review / chỉnh sửa</p><p>⑤ Publish theo kênh</p><p style="color:#d59a55">Outbound hiện OFF · Demo an toàn</p></aside></div>';
  echo '<h2>Content Queue</h2><table class="widefat striped"><thead><tr><th>Job</th><th>Project</th><th>Kênh</th><th>Trạng thái</th><th></th></tr></thead><tbody>'; if(!$jobs) echo '<tr><td colspan="5">Chưa có job. Tạo job đầu tiên ở trên.</td></tr>'; foreach($jobs as $j){$pid=(int)get_post_meta($j->ID,'hc_project_id',true);echo '<tr><td>'.esc_html($j->post_title).'</td><td>'.esc_html(get_the_title($pid)).'</td><td>'.esc_html(implode(', ',(array)get_post_meta($j->ID,'hc_channels',true))).'</td><td>'.esc_html(get_post_meta($j->ID,'hc_agent_status',true)?:'queued').'</td><td><a href="'.esc_url(get_edit_post_link($j->ID)).'">Review</a></td></tr>';} echo '</tbody></table></div>';
}
add_action('admin_post_hcdecor_create_content_job',function(){
  check_admin_referer('hcdecor_agent_job'); if(!current_user_can('edit_posts')) wp_die('Forbidden');
  $pid=(int)($_POST['project_id']??0); $brief=sanitize_textarea_field(wp_unslash($_POST['brief']??'')); $channels=array_values(array_intersect((array)($_POST['channels']??[]),['web','facebook','tiktok','youtube']));
  $id=wp_insert_post(['post_type'=>'hc_content_job','post_status'=>'publish','post_title'=>'AI Job · '.($pid?get_the_title($pid):'Content').' · '.current_time('Y-m-d H:i'),'post_content'=>$brief]);
  if($id&&!is_wp_error($id)){update_post_meta($id,'hc_project_id',$pid);update_post_meta($id,'hc_channels',$channels);update_post_meta($id,'hc_agent_status','queued');update_post_meta($id,'hc_outbound',false);}
  wp_safe_redirect(admin_url('admin.php?page=hcdecor-agent')); exit;
});


/* HCDECOR_AGENT_BRIDGE_V1 */
function hcdecor_bridge_token(){
  $t=(string)get_option('hcdecor_bridge_token','');
  if($t===''){ $t=wp_generate_password(48,false,false); update_option('hcdecor_bridge_token',$t,false); }
  return $t;
}
function hcdecor_bridge_auth(WP_REST_Request $r){
  $given=(string)$r->get_header('X-HCDecor-Bridge');
  return $given!=='' && hash_equals(hcdecor_bridge_token(),$given);
}
add_action('rest_api_init',function(){
  register_rest_route('hcdecor/v1','/bridge/status',[
    'methods'=>'GET','permission_callback'=>'hcdecor_bridge_auth',
    'callback'=>function(){return rest_ensure_response([
      'ok'=>true,'bridge'=>'HCDecor Agent Bridge','version'=>'1.0','outbound'=>false,
      'projects'=>(int)(wp_count_posts('hc_project')->publish??0),
      'media'=>(int)(wp_count_attachments()->inherit??0),
      'jobs'=>(int)(wp_count_posts('hc_content_job')->publish??0)
    ]);}
  ]);
  register_rest_route('hcdecor/v1','/bridge/projects',[
    'methods'=>'GET','permission_callback'=>'hcdecor_bridge_auth',
    'callback'=>function(){
      $ps=get_posts(['post_type'=>'hc_project','post_status'=>'publish','numberposts'=>100,'orderby'=>'modified','order'=>'DESC']);
      return rest_ensure_response(array_map(function($p){return [
        'id'=>$p->ID,'title'=>$p->post_title,'excerpt'=>$p->post_excerpt,
        'gallery'=>(array)get_post_meta($p->ID,'hc_gallery_ids',true),'modified'=>$p->post_modified
      ];},$ps));
    }
  ]);
  register_rest_route('hcdecor/v1','/bridge/jobs',[
    'methods'=>'GET','permission_callback'=>'hcdecor_bridge_auth',
    'callback'=>function(){
      $js=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>100,'orderby'=>'modified','order'=>'DESC']);
      return rest_ensure_response(array_map(function($j){return [
        'id'=>$j->ID,'title'=>$j->post_title,'brief'=>$j->post_content,
        'project_id'=>(int)get_post_meta($j->ID,'hc_project_id',true),
        'channels'=>(array)get_post_meta($j->ID,'hc_channels',true),
        'status'=>(string)get_post_meta($j->ID,'hc_agent_status',true),
        'outbound'=>false
      ];},$js));
    }
  ]);
  register_rest_route('hcdecor/v1','/bridge/jobs',[
    'methods'=>'POST','permission_callback'=>'hcdecor_bridge_auth',
    'callback'=>function(WP_REST_Request $r){
      $pid=(int)$r->get_param('project_id');
      $brief=sanitize_textarea_field((string)$r->get_param('brief'));
      $channels=array_values(array_intersect((array)$r->get_param('channels'),['web','facebook','tiktok','youtube']));
      $id=wp_insert_post(['post_type'=>'hc_content_job','post_status'=>'publish','post_title'=>'Bridge Job · '.($pid?get_the_title($pid):'Content').' · '.current_time('Y-m-d H:i'),'post_content'=>$brief]);
      if(is_wp_error($id)) return $id;
      update_post_meta($id,'hc_project_id',$pid); update_post_meta($id,'hc_channels',$channels);
      update_post_meta($id,'hc_agent_status','queued'); update_post_meta($id,'hc_outbound',false);
      return rest_ensure_response(['ok'=>true,'id'=>$id,'outbound'=>false]);
    }
  ]);
  register_rest_route('hcdecor/v1','/bridge/jobs/(?P<id>\\d+)',[
    'methods'=>'POST','permission_callback'=>'hcdecor_bridge_auth',
    'callback'=>function(WP_REST_Request $r){
      $id=(int)$r['id']; if(get_post_type($id)!=='hc_content_job') return new WP_Error('not_found','Job not found',['status'=>404]);
      foreach(['brief'=>'post_content','title'=>'post_title'] as $key=>$field){$v=$r->get_param($key);if($v!==null)wp_update_post(['ID'=>$id,$field=>sanitize_textarea_field((string)$v)]);}
      $status=$r->get_param('status'); if($status!==null) update_post_meta($id,'hc_agent_status',sanitize_key($status));
      return rest_ensure_response(['ok'=>true,'id'=>$id,'outbound'=>false]);
    }
  ]);
});

add_action('admin_menu',function(){
  add_submenu_page('hcdecor-hub','Agent Bridge','Agent Bridge','manage_options','hcdecor-bridge','hcdecor_bridge_admin',2);
},16);
function hcdecor_bridge_admin(){
  $token=hcdecor_bridge_token();
  echo '<div class="wrap"><h1>HCDecor Agent Bridge</h1><p><strong>Trạng thái:</strong> READY · Outbound OFF</p>';
  echo '<p>Bridge kết nối HUB Agent với Project, Media và Content Queue. Token chỉ dùng cho máy/agent được cấp quyền.</p>';
  echo '<table class="widefat striped" style="max-width:900px"><tr><th>Endpoint</th><td><code>'.esc_html(rest_url('hcdecor/v1/bridge/status')).'</code></td></tr><tr><th>Header</th><td><code>X-HCDecor-Bridge</code></td></tr><tr><th>Token</th><td><input type="password" readonly value="'.esc_attr($token).'" style="width:520px" onclick="this.type=\'text\';this.select()"></td></tr></table>';
  echo '<p><em>Không gửi token lên GitHub. Khi chuyển sang staging HTTPS, cùng Bridge này có thể được Agent truy cập từ xa.</em></p></div>';
}


/* HCDECOR_CONTENT_STUDIO_V1 */
add_action('admin_menu',function(){
  add_submenu_page('hcdecor-hub','Content Studio','Content Studio','edit_posts','hcdecor-studio','hcdecor_studio_admin',1);
},14);

function hcdecor_studio_admin(){
  $projects=get_posts(['post_type'=>'hc_project','post_status'=>'publish','numberposts'=>30,'orderby'=>'modified','order'=>'DESC']);
  $media=get_posts(['post_type'=>'attachment','post_status'=>'inherit','post_mime_type'=>'image','numberposts'=>12,'orderby'=>'date','order'=>'DESC']);
  echo '<div class="wrap hc-studio"><style>
  .hc-studio{max-width:1280px}.hc-studio-head{display:flex;justify-content:space-between;align-items:center;margin:10px 0 22px}.hc-badge{background:#111;color:#d59a55;padding:8px 12px;border-radius:999px}
  .hc-studio-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:18px}.hc-card{background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:20px}.hc-card h2{margin-top:0}
  .hc-projects{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.hc-project{border:1px solid #ddd;border-radius:10px;padding:12px;min-height:90px}.hc-project strong{display:block;margin-bottom:8px}
  .hc-media{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}.hc-media img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;background:#eee}
  .hc-channel{display:inline-block;padding:7px 10px;border:1px solid #ddd;border-radius:8px;margin:0 5px 5px 0}.hc-flow{display:flex;gap:8px;flex-wrap:wrap}.hc-flow span{background:#111;color:#fff;padding:10px 13px;border-radius:8px}
  @media(max-width:900px){.hc-studio-grid{grid-template-columns:1fr}.hc-projects{grid-template-columns:1fr}.hc-media{grid-template-columns:repeat(3,1fr)}}
  </style><div class="hc-studio-head"><div><h1>HCDecor HUB · Content Studio</h1><p>Project + Media → AI Agent → Preview → Publish</p></div><span class="hc-badge">DEMO · OUTBOUND OFF</span></div>';
  echo '<div class="hc-studio-grid"><section class="hc-card"><h2>1 · Chọn Project</h2><div class="hc-projects">';
  if(!$projects) echo '<p>Chưa có project.</p>'; foreach($projects as $p) echo '<div class="hc-project"><strong>'.esc_html($p->post_title).'</strong><small>'.esc_html($p->post_excerpt).'</small><p><a href="'.esc_url(get_edit_post_link($p->ID)).'">Media & dữ liệu →</a></p></div>';
  echo '</div><hr><h2>2 · Media gần đây</h2><div class="hc-media">';
  if(!$media) echo '<p>Chưa có ảnh. <a href="'.esc_url(admin_url('media-new.php')).'">Upload ảnh đầu tiên</a></p>'; foreach($media as $m){$src=wp_get_attachment_image_url($m->ID,'medium');if($src)echo '<a href="'.esc_url(get_edit_post_link($m->ID)).'"><img src="'.esc_url($src).'" alt=""></a>';}
  echo '</div><p><a class="button" href="'.esc_url(admin_url('media-new.php')).'">+ Upload Media</a> <a class="button" href="'.esc_url(admin_url('upload.php')).'">Media Library</a></p></section>';
  echo '<section class="hc-card"><h2>3 · AI Agent Brief</h2><form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="hcdecor_create_content_job">'.wp_nonce_field('hcdecor_agent_job','_wpnonce',true,false).'<p><select name="project_id" style="width:100%"><option value="0">Chọn Project</option>';foreach($projects as $p)echo '<option value="'.$p->ID.'">'.esc_html($p->post_title).'</option>';echo '</select></p><textarea name="brief" rows="7" style="width:100%" placeholder="Ví dụ: Phân tích hình ảnh dự án, viết nội dung Web và caption social; đề xuất ảnh cover, tiêu đề và CTA."></textarea><h3>Kênh preview</h3><label class="hc-channel"><input type="checkbox" name="channels[]" value="web" checked> Web</label><label class="hc-channel"><input type="checkbox" name="channels[]" value="facebook" checked> Facebook</label><label class="hc-channel"><input type="checkbox" name="channels[]" value="tiktok"> TikTok</label><label class="hc-channel"><input type="checkbox" name="channels[]" value="youtube"> YouTube</label><p><button class="button button-primary button-hero">Tạo Content Job</button></p></form><hr><h2>4 · Agent Flow</h2><div class="hc-flow"><span>Analyze</span><span>Generate</span><span>Review</span><span>Preview</span><span>Publish</span></div><p><em>Publish ra ngoài đang khóa. Có thể chỉnh content và preview an toàn.</em></p><p><a class="button" href="'.esc_url(admin_url('edit.php?post_type=hc_content_job')).'">Mở Content Queue</a></p></section></div></div>';
}

add_filter('manage_hc_content_job_posts_columns',function($c){return ['cb'=>$c['cb'],'title'=>'Content Job','project'=>'Project','channels'=>'Kênh','agent'=>'Agent status','date'=>'Ngày'];});
add_action('manage_hc_content_job_posts_custom_column',function($col,$id){
  if($col==='project') echo esc_html(get_the_title((int)get_post_meta($id,'hc_project_id',true)));
  if($col==='channels') echo esc_html(implode(' · ',(array)get_post_meta($id,'hc_channels',true)));
  if($col==='agent') echo '<strong>'.esc_html(get_post_meta($id,'hc_agent_status',true)?:'queued').'</strong>';
},10,2);


/* HCDECOR_CONTENT_STUDIO_V2: visual workspace + channel previews */
add_action('admin_menu',function(){
  add_submenu_page('hcdecor-hub','Studio Demo','Studio Demo','edit_posts','hcdecor-studio-v2','hcdecor_studio_v2_admin',0);
},13);

function hcdecor_studio_v2_admin(){
  $projects=get_posts(['post_type'=>'hc_project','post_status'=>'publish','numberposts'=>20,'orderby'=>'modified','order'=>'DESC']);
  $media=get_posts(['post_type'=>'attachment','post_status'=>'inherit','post_mime_type'=>'image','numberposts'=>18,'orderby'=>'date','order'=>'DESC']);
  $jobs=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>6,'orderby'=>'date','order'=>'DESC']);
  ?>
  <div class="wrap" id="hcStudio2">
  <style>
  #hcStudio2{max-width:1380px;color:#17191c}.hcs-top{display:flex;justify-content:space-between;align-items:center;margin:12px 0 20px}.hcs-top h1{font-size:30px;margin:0}.hcs-safe{background:#17191c;color:#d9a15d;padding:9px 13px;border-radius:999px;font-weight:700}
  .hcs-layout{display:grid;grid-template-columns:250px minmax(500px,1fr) 380px;gap:14px}.hcs-panel{background:#fff;border:1px solid #dcdcde;border-radius:14px;overflow:hidden}.hcs-title{padding:15px 17px;border-bottom:1px solid #eee;font-weight:700}.hcs-body{padding:14px}
  .hcs-project{padding:11px;border:1px solid #e3e3e3;border-radius:9px;margin-bottom:8px;cursor:pointer}.hcs-project:hover,.hcs-project.active{border-color:#d59a55;background:#fff9f2}.hcs-project small{display:block;color:#777;margin-top:4px}
  .hcs-media{display:grid;grid-template-columns:repeat(3,1fr);gap:7px}.hcs-media label{position:relative;cursor:pointer}.hcs-media img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;background:#eee}.hcs-media input{position:absolute;top:6px;left:6px}
  .hcs-brief textarea{width:100%;min-height:120px}.hcs-actions{display:flex;gap:7px;flex-wrap:wrap;margin:12px 0}.hcs-chip{border:1px solid #ddd;border-radius:999px;padding:7px 10px;background:#fff}
  .hcs-preview{background:#f5f6f7;border-radius:11px;padding:14px;margin-bottom:10px}.hcs-preview strong{display:block;margin-bottom:8px}.hcs-preview p{margin:5px 0;color:#555}.hcs-cover{aspect-ratio:16/9;border-radius:9px;background:linear-gradient(135deg,#161a1f,#3b2a1a);display:flex;align-items:end;padding:15px;color:#fff;font-size:20px;font-weight:700}
  .hcs-flow{display:flex;gap:6px;flex-wrap:wrap}.hcs-flow span{background:#17191c;color:#fff;padding:7px 9px;border-radius:7px;font-size:12px}.hcs-queue{margin-top:14px}.hcs-job{padding:9px 0;border-bottom:1px solid #eee}.hcs-job em{color:#b87932}
  @media(max-width:1100px){.hcs-layout{grid-template-columns:220px 1fr}.hcs-layout>.hcs-panel:last-child{grid-column:1/-1}}@media(max-width:782px){.hcs-layout{grid-template-columns:1fr}.hcs-layout>.hcs-panel:last-child{grid-column:auto}}
  </style>
  <div class="hcs-top"><div><h1>HCDecor · Content Studio</h1><p>Biến Project + Media thành nội dung đa nền tảng.</p></div><span class="hcs-safe">DEMO · PUBLISH OFF</span></div>
  <div class="hcs-layout">
    <section class="hcs-panel"><div class="hcs-title">① PROJECT</div><div class="hcs-body">
    <?php foreach($projects as $i=>$p): ?><div class="hcs-project <?php echo $i===0?'active':'';?>" data-project="<?php echo (int)$p->ID;?>"><strong><?php echo esc_html($p->post_title);?></strong><small><?php echo esc_html(wp_trim_words($p->post_excerpt,10));?></small></div><?php endforeach;?>
    <a class="button" href="<?php echo esc_url(admin_url('post-new.php?post_type=hc_project'));?>">+ Project</a>
    </div></section>
    <section class="hcs-panel"><div class="hcs-title">② MEDIA + AI BRIEF</div><div class="hcs-body">
      <div class="hcs-media"><?php if(!$media): ?><p>Chưa có ảnh.</p><?php endif; foreach($media as $m): $src=wp_get_attachment_image_url($m->ID,'medium'); if($src): ?><label><input type="checkbox" value="<?php echo (int)$m->ID;?>"><img src="<?php echo esc_url($src);?>"></label><?php endif; endforeach;?></div>
      <p><a class="button" href="<?php echo esc_url(admin_url('media-new.php'));?>">+ Upload ảnh/video</a> <a class="button" href="<?php echo esc_url(admin_url('upload.php'));?>">Media Library</a></p>
      <div class="hcs-brief"><textarea id="hcBrief" placeholder="Yêu cầu Agent: phân tích hình ảnh, chọn cover, viết bài dự án cho Web, caption Facebook, kịch bản TikTok/Reels..."></textarea></div>
      <div class="hcs-actions"><button class="button button-primary" id="hcDemoGenerate">✦ Generate Demo</button><span class="hcs-chip">Web</span><span class="hcs-chip">Facebook</span><span class="hcs-chip">TikTok / Reels</span><span class="hcs-chip">YouTube</span></div>
      <div class="hcs-flow"><span>ANALYZE MEDIA</span><span>SELECT COVER</span><span>WRITE</span><span>REVIEW</span><span>PREVIEW</span><span>PUBLISH</span></div>
      <div class="hcs-queue"><h3>Content Queue</h3><?php foreach($jobs as $j): ?><div class="hcs-job"><strong><?php echo esc_html($j->post_title);?></strong><br><em><?php echo esc_html(get_post_meta($j->ID,'hc_agent_status',true)?:'queued');?></em></div><?php endforeach;?></div>
    </div></section>
    <aside class="hcs-panel"><div class="hcs-title">③ LIVE PREVIEW</div><div class="hcs-body">
      <div class="hcs-preview"><strong>WEB · PROJECT</strong><div class="hcs-cover" id="hcCover">HCDecor Project Story</div><h2 id="hcWebTitle">Không gian được tạo nên từ ý tưởng</h2><p id="hcWebText">AI Agent sẽ tạo nội dung dự án từ brief và media đã chọn.</p></div>
      <div class="hcs-preview"><strong>FACEBOOK</strong><p id="hcFb">Một dự án mới từ HCDecor — nơi thiết kế, vật liệu và thi công cùng kể một câu chuyện.</p><small>#HCDecor #ThietKe #ThiCong</small></div>
      <div class="hcs-preview"><strong>TIKTOK / REELS</strong><p id="hcTk">Hook: Từ bản vẽ đến không gian thực tế trong 15 giây.</p><small>Cover → Before/After → Detail → CTA</small></div>
      <p><button class="button" disabled>Publish — đang khóa</button></p>
    </div></aside>
  </div>
  <script>
  (function(){const root=document.getElementById('hcStudio2');root.querySelectorAll('.hcs-project').forEach(x=>x.onclick=()=>{root.querySelectorAll('.hcs-project').forEach(y=>y.classList.remove('active'));x.classList.add('active')});document.getElementById('hcDemoGenerate').onclick=function(){const b=document.getElementById('hcBrief').value.trim();const p=root.querySelector('.hcs-project.active strong');const name=p?p.textContent:'HCDecor Project';document.getElementById('hcWebTitle').textContent=name;document.getElementById('hcWebText').textContent=b||'Thiết kế được phát triển từ nhu cầu thực tế, tập trung vào nhận diện, công năng và trải nghiệm không gian.';document.getElementById('hcFb').textContent='✨ '+name+' — '+(b||'HCDecor biến ý tưởng thành trải nghiệm không gian rõ nét và có tính ứng dụng.');document.getElementById('hcTk').textContent='Hook: '+name+' — xem quá trình từ ý tưởng → thiết kế → hoàn thiện.';document.getElementById('hcCover').textContent=name;};})();
  </script></div>
  <?php
}


/* HCDECOR_CONTENT_STUDIO_V3: persistent drafts + channel variants */
add_action('admin_post_hcdecor_studio_save',function(){
  check_admin_referer('hcdecor_studio_save');
  if(!current_user_can('edit_posts')) wp_die('Forbidden');
  $pid=(int)($_POST['project_id']??0);
  $media=array_values(array_filter(array_map('intval',(array)($_POST['media_ids']??[]))));
  $brief=sanitize_textarea_field(wp_unslash($_POST['brief']??''));
  $title=sanitize_text_field(wp_unslash($_POST['web_title']??''));
  $web=sanitize_textarea_field(wp_unslash($_POST['web_text']??''));
  $fb=sanitize_textarea_field(wp_unslash($_POST['facebook_text']??''));
  $tk=sanitize_textarea_field(wp_unslash($_POST['tiktok_text']??''));
  $id=wp_insert_post(['post_type'=>'hc_content_job','post_status'=>'publish','post_title'=>'Studio · '.($pid?get_the_title($pid):'Content').' · '.current_time('Y-m-d H:i'),'post_content'=>$brief]);
  if(!is_wp_error($id)){
    update_post_meta($id,'hc_project_id',$pid); update_post_meta($id,'hc_media_ids',$media);
    update_post_meta($id,'hc_agent_status','review'); update_post_meta($id,'hc_outbound',false);
    update_post_meta($id,'hc_web_title',$title); update_post_meta($id,'hc_web_text',$web);
    update_post_meta($id,'hc_facebook_text',$fb); update_post_meta($id,'hc_tiktok_text',$tk);
  }
  wp_safe_redirect(admin_url('admin.php?page=hcdecor-studio-v2&saved=1')); exit;
});
add_action('admin_footer',function(){
  if(!isset($_GET['page'])||$_GET['page']!=='hcdecor-studio-v2') return; ?>
  <script>
  (function(){
    const root=document.getElementById('hcStudio2'); if(!root)return;
    const btn=document.getElementById('hcDemoGenerate'); if(!btn)return;
    const save=document.createElement('button'); save.className='button button-primary'; save.textContent='Save to Content Queue'; save.style.marginLeft='8px';
    btn.parentNode.appendChild(save);
    save.onclick=function(e){e.preventDefault();
      const form=document.createElement('form');form.method='post';form.action='<?php echo esc_js(admin_url('admin-post.php')); ?>';
      const fields={action:'hcdecor_studio_save',_wpnonce:'<?php echo esc_js(wp_create_nonce('hcdecor_studio_save')); ?>',project_id:(root.querySelector('.hcs-project.active')||{}).dataset?.project||0,brief:(document.getElementById('hcBrief')||{}).value||'',web_title:(document.getElementById('hcWebTitle')||{}).textContent||'',web_text:(document.getElementById('hcWebText')||{}).textContent||'',facebook_text:(document.getElementById('hcFb')||{}).textContent||'',tiktok_text:(document.getElementById('hcTk')||{}).textContent||''};
      Object.entries(fields).forEach(([k,v])=>{const i=document.createElement('input');i.type='hidden';i.name=k;i.value=v;form.appendChild(i)});
      root.querySelectorAll('.hcs-media input:checked').forEach(x=>{const i=document.createElement('input');i.type='hidden';i.name='media_ids[]';i.value=x.value;form.appendChild(i)});
      document.body.appendChild(form);form.submit();
    };
    root.querySelectorAll('.hcs-preview p,.hcs-preview h2').forEach(x=>{x.contentEditable='true';x.title='Click để chỉnh trực tiếp';});
  })();
  </script><?php
});


/* HCDECOR_PRODUCTION_MODULES */
foreach (['background-sync.php','content-operations.php','project-publishing.php'] as $hcdecor_module) {
    $hcdecor_module_path = plugin_dir_path(__FILE__) . 'modules/' . $hcdecor_module;
    if (is_readable($hcdecor_module_path)) require_once $hcdecor_module_path;
}
