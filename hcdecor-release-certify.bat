@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HCDecor HUB Release Certification ===
call hcdecor-final-e2e.bat
if errorlevel 1 goto FAIL
call hcdecor-release-ready.bat
if errorlevel 1 goto FAIL
echo.
echo HCDECOR_RELEASE_CERTIFIED
echo Production mutation remains LOCKED until durable identity/approval/executor authority is bound.
exit /b 0
:FAIL
echo HCDECOR_RELEASE_CERTIFICATION_FAILED
exit /b 1
