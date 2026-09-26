@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HCDecor AUTO Runner
cd /d "%~dp0"

rem LocalWP WP-CLI environment recovery when launched outside Site Shell
where wp >nul 2>&1
if errorlevel 1 (
  if exist "%APPDATA%\\Local\\lightning-services" (
    for /d %%D in ("%APPDATA%\\Local\\lightning-services\\php-*") do set "PATH=%%~fD\\bin\\win64;!PATH!"
  )
)
where wp >nul 2>&1
if errorlevel 1 goto :need_shell
set "LOG=%CD%\hcdecor-auto.log"
set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress"
set "STAMP=%RANDOM%%RANDOM%"
set "PLUGIN=wp-content\plugins\hcdecor-core"
set "ASSETS=%PLUGIN%\assets"

echo ================================================== > "%LOG%"
echo HCDecor AUTO %date% %time% >> "%LOG%"
echo ================================================== >> "%LOG%"
call :say "HCDecor AUTO started"

call :run "Preflight WordPress" "wp core is-installed"
call :run "Check Elementor" "wp plugin is-active elementor"
call :run "Check Hello Elementor" "wp theme is-active hello-elementor"

if not exist "%PLUGIN%" mkdir "%PLUGIN%"
if not exist "%ASSETS%" mkdir "%ASSETS%"

call :need_shell
echo.
echo ================================================
echo HCDECOR AUTO: CAN THIEP 1 LAN
echo Runner dang duoc mo ngoai Local Site Shell nen WP-CLI chua duoc nap.
echo Trong LocalWP: HCDecor HUB ^> Site shell, sau do go: call hcdecor-auto.cmd
echo ================================================
pause
exit /b 2

:download "%BASE%/hcdecor-core/hcdecor-core.php?v=%STAMP%" "%PLUGIN%\hcdecor-core.php"
call :download "%BASE%/hcdecor-core/homepage-builder.php?v=%STAMP%" "%PLUGIN%\homepage-builder.php"
call :download "%BASE%/hcdecor-core/assets/hcdecor-elementor.css?v=%STAMP%" "%ASSETS%\hcdecor-elementor.css"
call :download "%BASE%/hcdecor-core/assets/hcdecor-homepage.css?v=%STAMP%" "%ASSETS%\hcdecor-homepage.css"

call :run "Activate HCDecor Core" "wp plugin activate hcdecor-core"
call :run "Site name" "wp option update blogname "HCDecor HUB""
call :run "Permalink" "wp option update permalink_structure "/%%postname%%/""
call :run "Flush rewrite" "wp rewrite flush"

for /f "delims=" %%I in ('wp eval "$p=get_page_by_path('trang-chu'); echo $p?$p->ID:'';"') do set "HOME_ID=%%I"
if not defined HOME_ID call :fail "Homepage missing"

call :run "Set static homepage" "wp option update show_on_front page"
call :run "Assign homepage" "wp option update page_on_front !HOME_ID!"
call :run "Build Elementor homepage" "wp eval-file %PLUGIN%\homepage-builder.php"

wp elementor flush-css >>"%LOG%" 2>&1
if errorlevel 1 echo [WARN] Elementor CLI CSS flush unavailable >>"%LOG%"

call :run "Validate core/pages/services/REST" "wp eval "$p=get_page_by_path('trang-chu');if(!$p)exit(11);$n=wp_count_posts('hc_service')->publish;if($n<4)exit(12);$r=rest_do_request('/hcdecor/v1/site');if($r->is_error()||$r->get_status()!=200)exit(13);$d=get_post_meta((int)get_option('page_on_front'),'_elementor_data',true);if(!$d||strlen($d)<500)exit(14);echo 'VALID';""

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

:run
call :say "%~1"
cmd /d /s /c "%~2" >>"%LOG%" 2>&1
if errorlevel 1 call :fail "%~1"
echo [PASS] %~1
exit /b 0

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
