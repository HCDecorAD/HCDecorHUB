@echo off
setlocal EnableExtensions
title HCDecor HUB - Start
cd /d "%~dp0"
set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress"
echo [HCDecor HUB] Installing/updating service manager...
curl.exe -fL "%BASE%/HCDecor-HUB-SERVICES.cmd?v=start" -o "HCDecor-HUB-SERVICES.cmd" || exit /b 1
curl.exe -fL "%BASE%/HCDecor-HUB-WATCH.cmd?v=start" -o "HCDecor-HUB-WATCH.cmd" || exit /b 1
del /q "hcdecor-hub-watch.lock" >nul 2>&1
call "HCDecor-HUB-SERVICES.cmd"
if errorlevel 2 exit /b 2
start "HCDecor HUB Background Sync" cmd /k ""%CD%\HCDecor-HUB-WATCH.cmd""
echo.
echo HCDECOR HUB STARTED
echo Background sync window can stay minimized.
echo AI Providers: http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-ai-providers
exit /b 0
