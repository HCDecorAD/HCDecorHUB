@echo off
setlocal EnableExtensions
title HCDecor HUB - Start
cd /d "%~dp0"
set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress"
echo [HCDecor HUB] Installing/updating service manager...
del /q "HCDecor-HUB-SERVICES.cmd.new" "HCDecor-HUB-WATCH.cmd.new" >nul 2>&1
curl.exe -fL "%BASE%/HCDecor-HUB-SERVICES.cmd?v=start" -o "HCDecor-HUB-SERVICES.cmd.new" || exit /b 1
curl.exe -fL "%BASE%/HCDecor-HUB-WATCH.cmd?v=start" -o "HCDecor-HUB-WATCH.cmd.new" || (del /q "HCDecor-HUB-SERVICES.cmd.new" >nul 2>&1 & exit /b 1)
move /y "HCDecor-HUB-SERVICES.cmd.new" "HCDecor-HUB-SERVICES.cmd" >nul || exit /b 1
move /y "HCDecor-HUB-WATCH.cmd.new" "HCDecor-HUB-WATCH.cmd" >nul || exit /b 1
del /q "hcdecor-hub-watch.lock" >nul 2>&1
call "HCDecor-HUB-SERVICES.cmd"
if errorlevel 2 exit /b 2
if "%HCDECOR_ENABLE_BACKGROUND_SYNC%"=="1" (
  start "HCDecor HUB Background Sync" cmd /k ""%CD%\HCDecor-HUB-WATCH.cmd""
  echo [AUTO] Background sync explicitly enabled.
) else (
  echo [SAFE] Background sync remains OFF. Use HCDecor-HUB-SYNC.cmd for manual updates.
)
echo.
echo HCDECOR HUB STARTED
echo AI Providers: http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-ai-providers
exit /b 0
