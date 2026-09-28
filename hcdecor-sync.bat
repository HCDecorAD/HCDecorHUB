@echo off
setlocal EnableExtensions
title HCDecor Local Recovery Sync
cd /d "%~dp0"
if "%~1"=="" (
 echo Usage: hcdecor-sync.bat "C:\path\to\local-wordpress-root" [additional options]
 echo Example: hcdecor-sync.bat "C:\Users\YOU\Local Sites\hcdecor-hub\app\public"
 exit /b 2
)
powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass -File "%~dp0hcdecor-sync.ps1" -WpRoot "%~1" %2 %3 %4 %5
exit /b %ERRORLEVEL%
