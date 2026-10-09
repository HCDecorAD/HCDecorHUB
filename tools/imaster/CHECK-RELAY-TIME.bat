@echo off
setlocal
powershell -NoProfile -File "D:\HCDecorHUB\repos\HCDecorHUB\tools\imaster\CHECK-RELAY-TIME.ps1"
exit /b %ERRORLEVEL%
