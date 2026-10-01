@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HCDecor HUB Operator Menu ===
if /i "%~1"=="foundation" goto FOUNDATION
if /i "%~1"=="e2e" goto E2E
if /i "%~1"=="ready" goto READY
if /i "%~1"=="certify" goto CERTIFY
if /i "%~1"=="release" goto RELEASE
if /i "%~1"=="hub" goto HUB
if /i "%~1"=="dr" goto DR
if /i "%~1"=="authority" goto AUTHORITY
echo Usage: %~nx0 foundation^|e2e^|ready^|certify^|release^|hub^|dr^|authority
exit /b 2
:AUTHORITY
call hcdecor-authority.bat
exit /b %ERRORLEVEL%
:DR
call hcdecor-dr.bat
exit /b %ERRORLEVEL%
:HUB
call hcdecor-foundation-full.bat
if errorlevel 1 exit /b %ERRORLEVEL%
call hcdecor-release-ready.bat
if errorlevel 1 exit /b %ERRORLEVEL%
echo HCDECOR_HUB_PASS
exit /b 0
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
