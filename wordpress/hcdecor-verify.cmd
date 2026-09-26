@echo off
setlocal EnableExtensions
title HCDecor VERIFY
cd /d "%~dp0"
set "LOG=%CD%\hcdecor-verify.log"
echo HCDecor VERIFY %date% %time% >"%LOG%"

where wp >nul 2>&1 || goto :need_shell
call wp core is-installed >>"%LOG%" 2>&1 || goto :fail
echo [PASS] WordPress

call wp plugin is-active elementor >>"%LOG%" 2>&1 || goto :fail
echo [PASS] Elementor
call wp theme is-active hello-elementor >>"%LOG%" 2>&1 || goto :fail
echo [PASS] Hello Elementor
call wp plugin is-active hcdecor-core >>"%LOG%" 2>&1 || goto :fail
echo [PASS] HCDecor Core

call wp eval "$p=get_page_by_path('trang-chu');if(!$p)exit(11);if((int)get_option('page_on_front')!==$p->ID)exit(12);$d=get_post_meta($p->ID,'_elementor_data',true);if(!$d||strlen($d)<500)exit(13);if(wp_count_posts('hc_service')->publish<4)exit(14);$r=rest_do_request('/hcdecor/v1/site');if($r->is_error()||$r->get_status()!=200)exit(15);echo 'VALID';" >>"%LOG%" 2>&1 || goto :fail
echo [PASS] Homepage + Services + REST

echo.
echo ================================================
echo HCDECOR VERIFY: PASS
echo Homepage: http://hcdecor-hub.local/
echo ================================================
exit /b 0

:need_shell
echo [FAIL] Open LocalWP Site Shell first.
exit /b 2
:fail
echo.
echo HCDECOR VERIFY: FAIL
echo Log: %LOG%
exit /b 1
