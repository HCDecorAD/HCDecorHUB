@echo off
setlocal
cd /d "%~dp0"
call hcdecor-release-full.bat
if errorlevel 1 goto FAIL
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\release-lock.ps1"
if errorlevel 1 goto FAIL
echo HCDECOR_FINAL_RELEASE_PASS_LOCK
exit /b 0
:FAIL
echo HCDECOR_FINAL_RELEASE_FAILED
exit /b 1
