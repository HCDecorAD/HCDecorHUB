@echo off
setlocal
cd /d "%~dp0"
echo === HCDecor Safe Release Checkpoint ===
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\release-checkpoint.ps1"
if errorlevel 1 exit /b %ERRORLEVEL%
call hcdecor-release-certify.bat
exit /b %ERRORLEVEL%
