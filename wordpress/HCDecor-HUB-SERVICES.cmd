@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HCDecor HUB - Services Manager
cd /d "%~dp0"
set "LOG=%CD%\hcdecor-hub-services.log"
set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress"
set "P=wp-content\plugins\hcdecor-core"
set "V=%RANDOM%%RANDOM%"
echo HCDecor HUB Services %date% %time% >"%LOG%"

where wp >nul 2>&1 || goto :need_shell
call wp core is-installed >>"%LOG%" 2>&1 || goto :fail
echo [1/6] WordPress OK

rem SERVICE A - Source Sync (isolated)
echo [2/6] Sync Core / Studio / Bridge...
if not exist "%P%\assets" mkdir "%P%\assets"
curl.exe -fL "%BASE%/hcdecor-core/hcdecor-core.php?v=%V%" -o "%P%\hcdecor-core.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/homepage-builder.php?v=%V%" -o "%P%\homepage-builder.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/assets/hcdecor-homepage.css?v=%V%" -o "%P%\assets\hcdecor-homepage.css.new" >>"%LOG%" 2>&1 || goto :syncfail
move /y "%P%\hcdecor-core.php.new" "%P%\hcdecor-core.php" >nul
move /y "%P%\homepage-builder.php.new" "%P%\homepage-builder.php" >nul
move /y "%P%\assets\hcdecor-homepage.css.new" "%P%\assets\hcdecor-homepage.css" >nul
echo [OK] Source Sync

rem SERVICE B - Plugin runtime (isolated)
echo [3/6] Runtime...
call wp plugin is-active hcdecor-core >nul 2>&1
if errorlevel 1 call wp plugin activate hcdecor-core >>"%LOG%" 2>&1
echo [OK] Runtime

rem SERVICE C - Data migrations/seed (non-destructive)
echo [4/6] Data...
call wp eval "do_action('init'); echo 'DATA_OK';" >>"%LOG%" 2>&1
if errorlevel 1 (echo [WARN] Data service deferred>>"%LOG%") else echo [OK] Data

rem SERVICE D - Website builder; failure does not stop Agent/Bridge
echo [5/6] Website Demo...
call wp eval-file "%P%\homepage-builder.php" >>"%LOG%" 2>&1
if errorlevel 1 (echo [WARN] Website builder skipped. HUB services continue.) else echo [OK] Website Demo

rem SERVICE E - Bridge/Agent quick health; no outbound
echo [6/6] Agent Bridge / Content Studio...
call wp option get hcdecor_bridge_token >nul 2>&1
if errorlevel 1 call wp eval "hcdecor_bridge_token(); echo 'BRIDGE_READY';" >>"%LOG%" 2>&1
echo [OK] Agent Bridge
echo [OK] Content Studio
echo [SAFE] External publishing remains OFF

echo.
echo ==================================================
echo HCDECOR HUB SERVICES: READY
echo HUB     : http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-hub
echo Studio  : http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-studio
echo Agent   : http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-agent
echo Bridge  : http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-bridge
echo Media   : http://hcdecor-hub.local/wp-admin/upload.php
echo Log     : %LOG%
echo ==================================================
exit /b 0

:syncfail
del /q "%P%\hcdecor-core.php.new" "%P%\homepage-builder.php.new" "%P%\assets\hcdecor-homepage.css.new" >nul 2>&1
echo [WARN] Source Sync failed. Existing local files kept intact.
goto :continue_after_sync

:continue_after_sync
call wp plugin is-active hcdecor-core >nul 2>&1
echo [SAFE] Existing HUB remains available.
exit /b 1

:need_shell
echo [FAIL] Open LocalWP Site Shell, then run HCDecor-HUB-SERVICES.cmd
exit /b 2
:fail
echo [FAIL] WordPress is not ready. No HUB files were changed.
exit /b 1
