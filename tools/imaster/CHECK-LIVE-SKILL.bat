@echo off
setlocal
set "HB=D:\HCDecorHUB\runtime\hcdr-relay-heartbeat.json"
if not exist "%HB%" (echo HEARTBEAT_MISSING&exit /b 21)
powershell -NoProfile -File "D:\HCDecorHUB\repos\HCDecorHUB\tools\imaster\CHECK-LIVE-SKILL.ps1"
exit /b %ERRORLEVEL%
