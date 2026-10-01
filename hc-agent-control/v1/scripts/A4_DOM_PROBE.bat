@echo off
setlocal
cd /d %~dp0..
if "%~1"=="" (echo Usage: A4_DOM_PROBE.bat CONVERSATION_ID&exit /b 365)
call scripts\A4_CHECK_TRANSPORT.bat || exit /b 366
python scripts\a4_dom_probe.py "%~1"
exit /b %errorlevel%
