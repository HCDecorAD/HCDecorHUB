@echo off
setlocal
cd /d "%~dp0"
node scripts\foundation-contract-check.mjs
if errorlevel 1 exit /b %ERRORLEVEL%
call hcdecor-foundation-check.bat
exit /b %ERRORLEVEL%
