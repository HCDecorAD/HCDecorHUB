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
$container('hc00001','hc-dark hc-hero',[
  $heading('hc10001','HCDECOR · HUB','h6'),
  $heading('hc10002','Ý tưởng tạo nên<br><span style="color:#d59a55">không gian khác biệt.</span>','h1'),
  $text('hc10003','<p>Thiết kế · Thi công bảng hiệu · Nội thất · 3D & Phối cảnh · Kiến trúc</p>'),
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
  $text('hc30003','<p id="du-an">Portfolio HCDecor sẽ được đồng bộ từ dữ liệu Dự án.</p>')
]),

$container('hc00004','hc-dark hc-process',[
  $heading('hc40001','QUY TRÌNH','h6'),
  $heading('hc40002','Đơn giản · Rõ ràng · Hiệu quả','h2'),
  $text('hc40003','<p>01 Tiếp nhận &nbsp;→&nbsp; 02 Khảo sát &nbsp;→&nbsp; 03 Thiết kế &nbsp;→&nbsp; 04 Thi công &nbsp;→&nbsp; 05 Bàn giao</p>')
]),

$container('hc00005','hc-dark hc-contact',[
  $heading('hc50001','Bắt đầu dự án cùng HCDecor','h2'),
  $text('hc50002','<p>231D An Dương Vương, P. An Lạc, Tp.HCM, Việt Nam<br>0888 821 842</p>'),
  $button('hc50003','Gọi HCDecor','tel:+84888821842'),
  $button('hc50004','Zalo','https://zalo.me/quangcaohocuong')
])
];
update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));
update_post_meta($id,'_elementor_edit_mode','builder');
update_post_meta($id,'_elementor_template_type','wp-page');
update_post_meta($id,'_wp_page_template','elementor_header_footer');
wp_update_post(['ID'=>$id,'post_status'=>'publish']);
echo "Homepage Elementor structure written.\n";
