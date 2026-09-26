@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HCDecor AUTO Runner
cd /d "%~dp0"

rem HCDecor AUTO must run inside LocalWP Site Shell so WP-CLI environment is loaded.
where wp >nul 2>&1
if errorlevel 1 (
  echo.
  echo ================================================
  echo HCDECOR AUTO: NEED LOCAL SITE SHELL
  echo Open LocalWP ^> HCDecor HUB ^> Site shell
  echo Then run: cd /d "C:\Users\DELL\Local Sites\hcdecor-hub\app\public" ^& call hcdecor-auto.cmd
  echo ================================================
  exit /b 2
)
set "LOG=%CD%\hcdecor-auto.log"
set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress"
set "STAMP=%RANDOM%%RANDOM%"
set "PLUGIN=wp-content\plugins\hcdecor-core"
set "ASSETS=%PLUGIN%\assets"

echo ================================================== > "%LOG%"
echo HCDecor AUTO %date% %time% >> "%LOG%"
echo ================================================== >> "%LOG%"
call :say "HCDecor AUTO started"

call :say "Preflight WordPress"
call wp core is-installed >>"%LOG%" 2>&1
if errorlevel 1 goto :fail_preflight
echo [PASS] Preflight WordPress

call :say "Check Elementor"
call wp plugin is-active elementor >>"%LOG%" 2>&1
if errorlevel 1 goto :fail_elementor
echo [PASS] Check Elementor

call :say "Check Hello Elementor"
call wp theme is-active hello-elementor >>"%LOG%" 2>&1
if errorlevel 1 goto :fail_hello
echo [PASS] Check Hello Elementor

if not exist "%PLUGIN%" mkdir "%PLUGIN%"
if not exist "%ASSETS%" mkdir "%ASSETS%"

call :download "%BASE%/hcdecor-core/hcdecor-core.php?v=%STAMP%" "%PLUGIN%\hcdecor-core.php"
call :download "%BASE%/hcdecor-core/homepage-builder.php?v=%STAMP%" "%PLUGIN%\homepage-builder.php"
call :download "%BASE%/hcdecor-core/assets/hcdecor-elementor.css?v=%STAMP%" "%ASSETS%\hcdecor-elementor.css"
call :download "%BASE%/hcdecor-core/assets/hcdecor-homepage.css?v=%STAMP%" "%ASSETS%\hcdecor-homepage.css"

call :say "Activate HCDecor Core"
call wp plugin activate hcdecor-core >>"%LOG%" 2>&1 || goto :fail_core
echo [PASS] Activate HCDecor Core

call :say "Site name"
call wp option update blogname "HCDecor HUB" >>"%LOG%" 2>&1 || goto :fail_site
echo [PASS] Site name

call :say "Permalink"
call wp option update permalink_structure "/%%postname%%/" >>"%LOG%" 2>&1 || goto :fail_permalink
echo [PASS] Permalink

call :say "Flush rewrite"
call wp rewrite flush >>"%LOG%" 2>&1 || goto :fail_rewrite
echo [PASS] Flush rewrite

for /f "delims=" %%I in ('call wp eval "$p=get_page_by_path('trang-chu'); echo $p?$p->ID:'';"') do set "HOME_ID=%%I"
if not defined HOME_ID goto :fail_home

call :say "Set static homepage"
call wp option update show_on_front page >>"%LOG%" 2>&1 || goto :fail_static
echo [PASS] Set static homepage

call :say "Assign homepage"
call wp option update page_on_front !HOME_ID! >>"%LOG%" 2>&1 || goto :fail_assign
echo [PASS] Assign homepage

call :say "Build Elementor homepage"
call wp eval-file "%PLUGIN%\homepage-builder.php" >>"%LOG%" 2>&1 || goto :fail_build
echo [PASS] Build Elementor homepage

rem Do not call "wp elementor flush-css" here: on LocalWP/Elementor it may block indefinitely.
call :say "Regenerate Elementor CSS cache"
call wp eval "if(class_exists('\\Elementor\\Plugin')){\\Elementor\\Plugin::$instance->files_manager->clear_cache();echo 'CSS_CACHE_CLEARED';}else{echo 'ELEMENTOR_NOT_LOADED';exit(21);}" >>"%LOG%" 2>&1
if errorlevel 1 (
  echo [WARN] Elementor CSS cache clear skipped
  echo [WARN] Elementor CSS cache clear skipped >>"%LOG%"
) else (
  echo [PASS] Regenerate Elementor CSS cache
)

call :say "Validate homepage/services/REST"
call wp eval "$p=get_page_by_path('trang-chu');if(!$p)exit(11);$n=wp_count_posts('hc_service')->publish;if($n<4)exit(12);$r=rest_do_request('/hcdecor/v1/site');if($r->is_error()||$r->get_status()!=200)exit(13);$d=get_post_meta((int)get_option('page_on_front'),'_elementor_data',true);if(!$d||strlen($d)<500)exit(14);echo 'VALID';" >>"%LOG%" 2>&1
if errorlevel 1 goto :fail_validate
echo [PASS] Validate homepage/services/REST

call :say "ALL AUTOMATED CHECKS PASS"
echo.
echo ================================================
echo HCDECOR AUTO: PASS
echo Homepage: http://hcdecor-hub.local/
echo Log: %LOG%
echo ================================================
echo.
choice /C YN /N /M "Open website now? [Y/N]: "
if errorlevel 2 exit /b 0
start "" "http://hcdecor-hub.local/"
exit /b 0

:download
call :say "Download %~2"
curl.exe -fL "%~1" -o "%~2" >>"%LOG%" 2>&1
if errorlevel 1 call :fail "Download failed: %~2"
exit /b 0

:fail_preflight
call :fail "Preflight WordPress"
exit /b 1
:fail_elementor
call :fail "Check Elementor"
exit /b 1
:fail_hello
call :fail "Check Hello Elementor"
exit /b 1
:fail_core
call :fail "Activate HCDecor Core"
exit /b 1
:fail_site
call :fail "Site name"
exit /b 1
:fail_permalink
call :fail "Permalink"
exit /b 1
:fail_rewrite
call :fail "Flush rewrite"
exit /b 1
:fail_home
call :fail "Homepage missing"
exit /b 1
:fail_static
call :fail "Set static homepage"
exit /b 1
:fail_assign
call :fail "Assign homepage"
exit /b 1
:fail_build
call :fail "Build Elementor homepage"
exit /b 1
:fail_validate
call :fail "Validate homepage/services/REST"
exit /b 1

:say
echo [AUTO] %~1
echo [AUTO] %~1 >>"%LOG%"
exit /b 0

:fail
echo.
echo ================================================
echo HCDECOR AUTO: FAIL - %~1
echo Log: %LOG%
echo ================================================
echo.
choice /C RCQ /N /M "Can thiep: [R]etry  [C]ontinue manual  [Q]uit: "
if errorlevel 3 exit /b 1
if errorlevel 2 (
  start notepad "%LOG%"
  exit /b 1
)
echo Retry requested. Re-run hcdecor-auto.cmd after checking the log.
pause
exit /b 1
