@echo off
setlocal
powershell -NoProfile -ExecutionPolicy Bypass -File "D:\HCDecorHUB\repos\HCDecorHUB\tools\visual-builder\INSPECT-PUBLISH.ps1"
exit /b %ERRORLEVEL%
