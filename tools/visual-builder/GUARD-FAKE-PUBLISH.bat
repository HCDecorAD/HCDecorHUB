@echo off
setlocal
powershell -NoProfile -ExecutionPolicy Bypass -File "D:\HCDecorHUB\repos\HCDecorHUB\tools\visual-builder\GUARD-FAKE-PUBLISH.ps1"
if errorlevel 1 exit /b %ERRORLEVEL%
cd /d D:\HCDecorHUB\HC_Visual_Builder
call npm run build
if errorlevel 1 exit /b %ERRORLEVEL%
call npm run lint
exit /b %ERRORLEVEL%
