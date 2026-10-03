@echo off
setlocal
cd /d "%~dp0"
call hc-group-done.bat
if errorlevel 1 exit /b 1
call hcdecor-final-release.bat
if errorlevel 1 exit /b 1
echo HC_DONE_D06_PASS final_freeze=1
