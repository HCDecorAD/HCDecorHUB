@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HCDecor Pre-Public Gate ===
call hcdecor-authority.bat
if errorlevel 1 goto FAIL
call hcdecor-final-e2e.bat
if errorlevel 1 goto FAIL
call hcdecor-release-ready.bat
if errorlevel 1 goto FAIL
echo.
echo HCDECOR_PREPUBLIC_PASS_LOCKED
echo This verifies source/runtime readiness only. Production mutation remains approval-gated.
exit /b 0
:FAIL
echo HCDECOR_PREPUBLIC_FAIL
exit /b 1
