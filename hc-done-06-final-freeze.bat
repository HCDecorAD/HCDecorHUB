@echo off
setlocal
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File scripts\done\final-freeze-v2.ps1
if errorlevel 1 exit /b %errorlevel%
call hcdecor-final-release.bat
if errorlevel 1 exit /b %errorlevel%
echo HC_DONE_D06_PASS final_freeze=manifest-verified
exit /b 0
