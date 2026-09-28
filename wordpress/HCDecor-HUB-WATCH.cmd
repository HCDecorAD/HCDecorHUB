@echo off
setlocal EnableExtensions
title HCDecor HUB - Background Sync
cd /d "%~dp0"
set "INTERVAL=60"
set "LOCK=%CD%\hcdecor-hub-watch.lock"
set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress"
if not "%HCDECOR_ENABLE_BACKGROUND_SYNC%"=="1" (
  echo [BLOCKED] Background auto-sync is OFF by default.
  echo Set HCDECOR_ENABLE_BACKGROUND_SYNC=1 only when continuous source updates are explicitly approved.
  exit /b 3
)
if /I not "%HCDECOR_APPROVE_CODE_SYNC%"=="1" (
  echo [BLOCKED] Background code sync also requires HCDECOR_APPROVE_CODE_SYNC=1.
  exit /b 3
)
if exist "%LOCK%" (echo HCDecor HUB Watch is already running. & exit /b 0)
echo %date% %time%>"%LOCK%"
echo HCDecor HUB Background Sync started.
echo Interval: %INTERVAL%s
echo Close this window to stop.
:loop
del /q "%CD%\HCDecor-HUB-SERVICES.cmd.new" >nul 2>&1
curl.exe -fsSL "%BASE%/HCDecor-HUB-SERVICES.cmd?v=%RANDOM%%RANDOM%" -o "%CD%\HCDecor-HUB-SERVICES.cmd.new" >nul 2>&1
if errorlevel 1 (
  echo [%date% %time%] Download failed; existing service manager retained.
) else (
  move /y "%CD%\HCDecor-HUB-SERVICES.cmd.new" "%CD%\HCDecor-HUB-SERVICES.cmd" >nul
  call "%CD%\HCDecor-HUB-SERVICES.cmd"
)
echo [%date% %time%] Next sync in %INTERVAL%s
timeout /t %INTERVAL% /nobreak >nul
goto loop
