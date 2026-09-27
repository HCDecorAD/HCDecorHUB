<?php
if (!defined('ABSPATH')) exit;
$id=(int)get_option('page_on_front');
if(!$id) throw new Exception('No front page');
$mk=function($id,$widget,$settings){return ['id'=>$id,'elType'=>'widget','widgetType'=>$widget,'settings'=>$settings,'elements'=>[]];};
$heading=function($id,$title,$tag='h2')use($mk){return $mk($id,'heading',['title'=>$title,'header_size'=>$tag]);};
$text=function($id,$html)use($mk){return $mk($id,'text-editor',['editor'=>$html]);};
$button=function($id,$label,$url)use($mk){return $mk($id,'button',['text'=>$label,'link'=>['url'=>$url],'size'=>'md']);};
$container=function($id,$class,$els,$settings=[])use(&$container){return ['id'=>$id,'elType'=>'container','settings'=>array_merge(['css_classes'=>$class,'content_width'=>'boxed'], $settings),'elements'=>$els];};

$data=[
$container('hc00001','hc-dark hc-hero hc-demo-hero',[
  $heading('hc10001','HCDECOR · HUB','h6'),
  $heading('hc10002','Ý tưởng tạo nên<br><span style="color:#d59a55">không gian khác biệt.</span>','h1'),
  $text('hc10003','<p class="hc-kicker">ONE HUB · DESIGN → BUILD → GROW</p><p>Thiết kế · Thi công bảng hiệu · Nội thất · 3D & Phối cảnh · Kiến trúc</p>'),
  $button('hc10004','Xem dự án','#du-an'),
  $button('hc10005','Liên hệ ngay','tel:+84888821842')
],['min_height'=>['unit'=>'vh','size'=>82]]),

$container('hc00002','hc-dark hc-services',[
  $heading('hc20001','DỊCH VỤ','h6'),
  $heading('hc20002','Giải pháp toàn diện cho công trình','h2'),
  $container('hc20010','hc-service-grid',[
    $heading('hc20101','Bảng hiệu','h3'),$heading('hc20102','Nội thất','h3'),
    $heading('hc20103','3D & Phối cảnh','h3'),$heading('hc20104','Kiến trúc','h3')
  ])
]),

$container('hc00003','hc-dark hc-projects',[
  $heading('hc30001','DỰ ÁN','h6'),
  $heading('hc30002','Công trình & ý tưởng nổi bật','h2'),
  $container('hc30010','hc-project-grid',[
    $container('hc30100','hc-project-card',[$heading('hc30101','Bảng hiệu','h3'),$text('hc30102','<p>Nhận diện thương hiệu · Mặt dựng · Biển quảng cáo</p>')]),
    $container('hc30200','hc-project-card',[$heading('hc30201','Nội thất','h3'),$text('hc30202','<p>Không gian kinh doanh · Văn phòng · Nhà ở</p>')]),
    $container('hc30300','hc-project-card',[$heading('hc30301','3D & Kiến trúc','h3'),$text('hc30302','<p>Phối cảnh · Concept · Giải pháp không gian</p>')])
  ]),
  $text('hc30003','<p id="du-an">Portfolio HCDecor được quản lý độc lập trong Website Data và có thể cập nhật mà không phá layout.</p>')
]),

$container('hc00004','hc-dark hc-process',[
  $heading('hc40001','QUY TRÌNH','h6'),
  $heading('hc40002','Đơn giản · Rõ ràng · Hiệu quả','h2'),
  $container('hc40010','hc-process-grid',[
    $text('hc40101','<p><strong>01</strong><br>Tiếp nhận</p>'),
    $text('hc40102','<p><strong>02</strong><br>Khảo sát</p>'),
    $text('hc40103','<p><strong>03</strong><br>Thiết kế</p>'),
    $text('hc40104','<p><strong>04</strong><br>Thi công</p>'),
    $text('hc40105','<p><strong>05</strong><br>Bàn giao</p>')
  ])
]),

$container('hc00006','hc-dark hc-hub-demo',[
  $heading('hc60001','HCDECOR HUB','h6'),
  $heading('hc60002','Một trung tâm để vận hành toàn bộ hệ sinh thái','h2'),
  $container('hc60010','hc-hub-grid',[
    $container('hc60100','hc-hub-card',[$heading('hc60101','01 · Website','h3'),$text('hc60102','<p>Elementor visual builder · Website Data · Portfolio động</p>')]),
    $container('hc60200','hc-hub-card',[$heading('hc60201','02 · Lead','h3'),$text('hc60202','<p>Form báo giá · Khách hàng · Trạng thái xử lý</p>')]),
    $container('hc60300','hc-hub-card',[$heading('hc60301','03 · Content','h3'),$text('hc60302','<p>Dự án → nội dung → thư viện media → social-ready</p>')]),
    $container('hc60400','hc-hub-card',[$heading('hc60401','04 · Automation','h3'),$text('hc60402','<p>Webhook-ready · lịch tác vụ · kiểm tra an toàn trước publish</p>')])
  ]),
  $button('hc60003','Yêu cầu báo giá','#bao-gia')
]),
$container('hc00005','hc-dark hc-contact',[
  $heading('hc50001','Bắt đầu dự án cùng HCDecor','h2'),
  $text('hc50000','<p id="bao-gia" class="hc-contact-label">TƯ VẤN · KHẢO SÁT · BÁO GIÁ</p>'),
  $text('hc50002','<p>231D An Dương Vương, P. An Lạc, Tp.HCM, Việt Nam<br>0888 821 842</p>'),
  $button('hc50003','Gọi HCDecor','tel:+84888821842'),
  $button('hc50004','Zalo','https://zalo.me/quangcaohocuong'),
  $text('hc50005','<div class="hc-demo-quote"><h3>Demo yêu cầu báo giá</h3><p>Khách hàng nhập thông tin → HCDecor HUB nhận lead → tạo báo giá → theo dõi xử lý.</p><p><strong>Họ tên</strong> · <strong>Số điện thoại</strong> · <strong>Dịch vụ</strong> · <strong>Nội dung yêu cầu</strong></p></div>')
])
];
update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));
update_post_meta($id,'_elementor_edit_mode','builder');
update_post_meta($id,'_elementor_template_type','wp-page');
update_post_meta($id,'_wp_page_template','elementor_header_footer');
wp_update_post(['ID'=>$id,'post_status'=>'publish']);
echo "Homepage Elementor structure written.\n";
