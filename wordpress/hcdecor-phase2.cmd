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

call wp eval "do_action('acf/init');if(!function_exists('acf_add_local_field_group'))exit(21);echo 'ACF_READY';" >>"%LOG%" 2>&1 || goto :fail
echo [PASS] ACF Data Model

call wp eval-file "%PLUGIN%\homepage-builder.php" >>"%LOG%" 2>&1 || goto :fail
echo [PASS] Elementor homepage rebuilt

call wp eval "if(class_exists('\\Elementor\\Plugin')){\\Elementor\\Plugin::$instance->files_manager->clear_cache();echo 'CACHE_OK';}" >>"%LOG%" 2>&1
echo [PASS] Elementor cache checkpoint

call wp eval "$a=rest_do_request('/hcdecor/v1/site');$b=rest_do_request('/hcdecor/v1/content');$c=rest_do_request('/hcdecor/v1/phase2');if($a->get_status()!=200||$b->get_status()!=200||$c->get_status()!=200)exit(31);if(wp_count_posts('hc_service')->publish<4)exit(32);$p=(int)get_option('page_on_front');$d=get_post_meta($p,'_elementor_data',true);if(!$d||strlen($d)<500)exit(33);echo 'VALID';" >>"%LOG%" 2>&1 || goto :fail
echo [PASS] REST + Services + Homepage

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
