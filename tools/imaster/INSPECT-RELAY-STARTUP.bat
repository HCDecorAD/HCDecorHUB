@echo off
setlocal
set "ROOT=D:\HCDecorHUB"
set "OUT=%ROOT%\evidence\imaster-skill-binding"
if not exist "%OUT%" mkdir "%OUT%"
powershell -NoProfile -File "%ROOT%\repos\HCDecorHUB\tools\imaster\INSPECT-RELAY-STARTUP.ps1"
exit /b %ERRORLEVEL%
