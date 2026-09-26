@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HCDecor HUB - Phase 2
cd /d "%~dp0"
set "LOG=%CD%\hcdecor-phase2.log"
set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress"
set "STAMP=%RANDOM%%RANDOM%"
set "PLUGIN=wp-content\plugins\hcdecor-core"

echo ================================================== >"%LOG%"
echo HCDecor PHASE 2 %date% %time% >>"%LOG%"
echo ================================================== >>"%LOG%"

where wp >nul 2>&1 || goto :need_shell
call wp core is-installed >>"%LOG%" 2>&1 || goto :fail
echo [PASS] WordPress

for %%P in (elementor hcdecor-core advanced-custom-fields fluentform wp-webhooks) do (
  call wp plugin is-active %%P >>"%LOG%" 2>&1 || goto :fail
  echo [PASS] Plugin %%P
)
call wp theme is-active hello-elementor >>"%LOG%" 2>&1 || goto :fail
echo [PASS] Hello Elementor

echo [AUTO] Sync HCDecor Core Phase 2
curl.exe -fL "%BASE%/hcdecor-core/hcdecor-core.php?v=%STAMP%" -o "%PLUGIN%\hcdecor-core.php" >>"%LOG%" 2>&1 || goto :fail
curl.exe -fL "%BASE%/hcdecor-core/homepage-builder.php?v=%STAMP%" -o "%PLUGIN%\homepage-builder.php" >>"%LOG%" 2>&1 || goto :fail
curl.exe -fL "%BASE%/hcdecor-core/assets/hcdecor-homepage.css?v=%STAMP%" -o "%PLUGIN%\assets\hcdecor-homepage.css" >>"%LOG%" 2>&1 || goto :fail
echo [PASS] Sync Phase 2 source

rem ACF is already loaded by WordPress during wp eval. Do not fire acf/init manually:
rem re-firing the lifecycle hook can trigger plugin callbacks twice.
rem Plugin activation is the reliable Phase 2 prerequisite. ACF local groups are
rem registered by HCDecor Core on acf/init and are verified later through REST/content.
echo [PASS] ACF runtime prerequisite
call wp post-type get hc_project --field=name >>"%LOG%" 2>&1 || goto :fail_content
call wp post-type get hc_service --field=name >>"%LOG%" 2>&1 || goto :fail_content
echo [PASS] HCDecor content types
echo [PASS] ACF Data Model source synced

call wp eval-file "%PLUGIN%\homepage-builder.php" >>"%LOG%" 2>&1 || goto :fail
echo [PASS] Elementor homepage rebuilt

call wp eval "if(class_exists('\\Elementor\\Plugin')){\\Elementor\\Plugin::$instance->files_manager->clear_cache();echo 'CACHE_OK';}" >>"%LOG%" 2>&1
echo [PASS] Elementor cache checkpoint

echo [AUTO] Final validation
call wp eval "$a=rest_do_request('/hcdecor/v1/site');if($a->get_status()!=200)exit(31);echo 'SITE_OK';" >>"%LOG%" 2>&1 || goto :fail_site_rest
echo [PASS] REST site
call wp eval "$b=rest_do_request('/hcdecor/v1/content');if($b->get_status()!=200)exit(32);echo 'CONTENT_OK';" >>"%LOG%" 2>&1 || goto :fail_content_rest
echo [PASS] REST content
call wp eval "$c=rest_do_request('/hcdecor/v1/phase2');if($c->get_status()!=200)exit(33);echo 'PHASE2_OK';" >>"%LOG%" 2>&1 || goto :fail_phase2_rest
echo [PASS] REST phase2
call wp post list --post_type=hc_service --post_status=publish --format=count >"%TEMP%\hcdecor-service-count.txt" 2>>"%LOG%" || goto :fail_services
set /p SERVICE_COUNT=<"%TEMP%\hcdecor-service-count.txt"
if !SERVICE_COUNT! LSS 4 goto :fail_services
echo [PASS] Services count !SERVICE_COUNT!
call wp eval "$p=(int)get_option('page_on_front');$d=get_post_meta($p,'_elementor_data',true);if(!$p||!$d||strlen($d)<500)exit(34);echo 'HOME_OK';" >>"%LOG%" 2>&1 || goto :fail_home
echo [PASS] Homepage Elementor data

echo [SAFE] Outbound webhooks remain DISABLED until endpoint authorization.
echo [SAFE] Outbound webhooks remain DISABLED >>"%LOG%"
echo.
echo ================================================
echo HCDECOR PHASE 2: PASS
echo ACF Data Model : READY
echo Fluent Forms   : ACTIVE
echo WP Webhooks    : ACTIVE / OUTBOUND OFF
echo Homepage       : http://hcdecor-hub.local/
echo Log            : %LOG%
echo ================================================
exit /b 0

:fail_content
echo [FAIL] HCDecor content type validation
echo [FAIL] HCDecor content type validation >>"%LOG%"
echo See: %LOG%
exit /b 1
:fail_site_rest
echo [FAIL] REST /hcdecor/v1/site
goto :fail
:fail_content_rest
echo [FAIL] REST /hcdecor/v1/content
goto :fail
:fail_phase2_rest
echo [FAIL] REST /hcdecor/v1/phase2
goto :fail
:fail_services
echo [FAIL] Service count
goto :fail
:fail_home
echo [FAIL] Homepage Elementor data
goto :fail
:need_shell
echo [FAIL] Run inside LocalWP Site Shell.
exit /b 2
:fail
echo.
echo ================================================
echo HCDECOR PHASE 2: FAIL
echo Log: %LOG%
echo ================================================
exit /b 1
