@echo off
setlocal EnableExtensions
echo == HCDecor Windows bootstrap ==

call wp core is-installed || goto :fail
echo [PASS] WordPress

call wp theme install hello-elementor --activate >nul 2>&1
call wp theme is-active hello-elementor || goto :fail
call wp plugin activate elementor >nul 2>&1
call wp plugin is-active elementor || goto :fail
call wp plugin activate hcdecor-core >nul 2>&1
call wp plugin is-active hcdecor-core || goto :fail
echo [PASS] Theme and plugins

call wp option update permalink_structure "/%%postname%%/" >nul || goto :fail
call wp rewrite flush >nul 2>&1
call wp option update blogname "HCDecor HUB" >nul || goto :fail
call wp option update blogdescription "Thiet ke - Thi cong - Noi that - Kien truc - 3D" >nul || goto :fail
call wp option update elementor_disable_color_schemes yes >nul
call wp option update elementor_disable_typography_schemes yes >nul
echo [PASS] Site configuration

for /f "delims=" %%I in ('wp eval "$p=get_page_by_path('trang-chu'); echo $p ? $p->ID : '';"') do set HOME_ID=%%I
if not defined HOME_ID goto :fail
call wp option update show_on_front page >nul || goto :fail
call wp option update page_on_front %HOME_ID% >nul || goto :fail
echo [PASS] Homepage ID %HOME_ID%

call wp eval "$s=['trang-chu','gioi-thieu','dich-vu-hcdecor','du-an-hcdecor','lien-he']; foreach($s as $x){if(!get_page_by_path($x)){fwrite(STDERR,'Missing page '.$x.PHP_EOL);exit(1);}}" || goto :fail
echo [PASS] Required pages

call wp eval "$items=[['Bảng hiệu','Thiết kế và thi công bảng hiệu, mặt dựng và nhận diện không gian.'],['Nội thất','Thiết kế và triển khai nội thất theo nhu cầu sử dụng thực tế.'],['3D & Phối cảnh','Phối cảnh 3D giúp hình dung phương án trước khi triển khai.'],['Kiến trúc','Giải pháp kiến trúc cân bằng thẩm mỹ, công năng và khả năng thi công.']]; foreach($items as $i){if(!get_page_by_title($i[0],OBJECT,'hc_service')){wp_insert_post(['post_type'=>'hc_service','post_status'=>'publish','post_title'=>$i[0],'post_excerpt'=>$i[1],'post_content'=>$i[1]]);}}" || goto :fail
call wp eval "$n=wp_count_posts('hc_service')->publish; if($n<4){fwrite(STDERR,'Services count: '.$n.PHP_EOL);exit(1);}" || goto :fail
echo [PASS] Services seeded and validated

call wp eval "$r=rest_do_request('/hcdecor/v1/site'); if($r->is_error() || $r->get_status()!==200){exit(1);} echo 'REST 200'.PHP_EOL;" || goto :fail
echo [PASS] REST API

call wp post meta update %HOME_ID% _wp_page_template elementor_header_footer >nul || goto :fail
call wp post meta update %HOME_ID% _elementor_edit_mode builder >nul || goto :fail
echo [PASS] Elementor homepage ready

echo.
echo ========================================
echo HCDECOR BOOTSTRAP: PASS
echo Homepage ID: %HOME_ID%
echo ========================================
exit /b 0

:fail
echo.
echo ========================================
echo HCDECOR BOOTSTRAP: FAIL
echo ========================================
exit /b 1
