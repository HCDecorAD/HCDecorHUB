@echo off
setlocal
cd /d %~dp0..
set OUT=dist\HC-Agent-Control-V1-Staging
if exist "%OUT%" rmdir /s /q "%OUT%"
mkdir "%OUT%" || exit /b 51
xcopy src "%OUT%\src\" /E /I /Y >nul || exit /b 52
xcopy scripts "%OUT%\scripts\" /E /I /Y >nul || exit /b 53
xcopy docs "%OUT%\docs\" /E /I /Y >nul 2>nul
mkdir "%OUT%\data" 2>nul
> "%OUT%\data\chats.json" echo {"version":1,"chats":{}}
> "%OUT%\data\queue.json" echo {"items":[],"global_paused":true,"paused_aliases":[]}
copy /Y HC_AutoChat.bat "%OUT%\" >nul
copy /Y manifest.json "%OUT%\" >nul
copy /Y release-gates.json "%OUT%\" >nul
echo STAGING_PACKAGE_PASS clean-data safe-paused
exit /b 0
