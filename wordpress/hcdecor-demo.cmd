@echo off
setlocal EnableExtensions
title HCDecor HUB DEMO
cd /d "%~dp0"
set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress/hcdecor-core"
set "P=wp-content\plugins\hcdecor-core"
set "V=demo-%RANDOM%%RANDOM%"
where wp >nul 2>&1 || (echo [FAIL] Open LocalWP Site Shell & exit /b 2)
echo [DEMO] Sync source through safe service manager...
call "%~dp0HCDecor-HUB-SERVICES.cmd"
if errorlevel 1 goto :fail
echo [DEMO] Homepage write gate...
if "%HCDECOR_APPROVE_HOMEPAGE_WRITE%"=="1" (
  call wp eval "define('HCDECOR_APPROVE_HOMEPAGE_WRITE',true); require '%P%/homepage-builder.php';" >nul 2>&1 || goto :fail
) else (
  echo [SAFE] Homepage rebuild skipped. Set HCDECOR_APPROVE_HOMEPAGE_WRITE=1 to approve it.
)
echo.
echo ================================================
echo HCDECOR HUB DEMO: READY
echo Website: http://hcdecor-hub.local/
echo HUB Admin: http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-hub
echo Media: http://hcdecor-hub.local/wp-admin/upload.php
echo Projects: http://hcdecor-hub.local/wp-admin/edit.php?post_type=hc_project
echo Quotes: http://hcdecor-hub.local/wp-admin/edit.php?post_type=hc_quote
echo ================================================
exit /b 0
:fail
echo [FAIL] Demo sync/build. Existing website remains intact.
exit /b 1
