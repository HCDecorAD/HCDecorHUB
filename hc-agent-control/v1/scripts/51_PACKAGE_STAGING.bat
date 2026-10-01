@echo off
setlocal
cd /d %~dp0..
set OUT=dist\HC-Agent-Control-V1-Staging
if exist "%OUT%" rmdir /s /q "%OUT%"
mkdir "%OUT%"
xcopy src "%OUT%\src\" /E /I /Y >nul
xcopy data "%OUT%\data\" /E /I /Y >nul 2>nul
copy /Y HC_AutoChat.bat "%OUT%\" >nul
copy /Y manifest.json "%OUT%\" >nul
copy /Y release-gates.json "%OUT%\" >nul
echo STAGING_PACKAGE_PASS
