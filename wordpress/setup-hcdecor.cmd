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

call wp rewrite structure "/%postname%/" >nul || goto :fail
call wp rewrite flush >nul 2>&1 || echo [WARN] Rewrite flush skipped on Local nginx
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

call wp eval "$s=['Bang hieu'=>'Bảng hiệu','Noi that'=>'Nội thất','3D'=>'3D & Phối cảnh','Kien truc'=>'Kiến trúc']; foreach($s as $label=>$x){if(!get_page_by_title($x,OBJECT,'hc_service')){fwrite(STDERR,'Missing service '.$label.PHP_EOL);exit(1);}}" || goto :fail
echo [PASS] Services

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
