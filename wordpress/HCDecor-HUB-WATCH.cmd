@echo off
setlocal EnableExtensions
title HCDecor HUB - Background Sync
cd /d "%~dp0"
set "INTERVAL=60"
set "LOCK=%CD%\hcdecor-hub-watch.lock"
if exist "%LOCK%" (echo HCDecor HUB Watch is already running. & exit /b 0)
echo %date% %time%>"%LOCK%"
echo HCDecor HUB Background Sync started.
echo Interval: %INTERVAL%s
echo Close this window to stop.
:loop
call "%CD%\HCDecor-HUB-SERVICES.cmd"
echo [%date% %time%] Next sync in %INTERVAL%s
timeout /t %INTERVAL% /nobreak >nul
goto loop
