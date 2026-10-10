@echo off
powershell -NoProfile -ExecutionPolicy Bypass -File "D:\HCDecorHUB\repos\HCDecorHUB\tools\visual-builder\VERIFY-PUBLISH-GUARD.ps1"
exit /b %ERRORLEVEL%
