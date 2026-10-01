@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HCDecor HUB Operator Menu ===
if /i "%~1"=="foundation" goto FOUNDATION
if /i "%~1"=="e2e" goto E2E
if /i "%~1"=="ready" goto READY
if /i "%~1"=="certify" goto CERTIFY
if /i "%~1"=="release" goto RELEASE
echo Usage: %~nx0 foundation^|e2e^|ready^|certify^|release
exit /b 2
:FOUNDATION
call hcdecor-foundation-full.bat
exit /b %ERRORLEVEL%
:E2E
call hcdecor-final-e2e.bat
exit /b %ERRORLEVEL%
:READY
call hcdecor-release-ready.bat
exit /b %ERRORLEVEL%
:CERTIFY
call hcdecor-release-certify.bat
exit /b %ERRORLEVEL%
:RELEASE
call hcdecor-final-release.bat
exit /b %ERRORLEVEL%
