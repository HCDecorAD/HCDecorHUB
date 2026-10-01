@echo off
setlocal
set SRC=%~dp0..
set DST=D:\HCDecorHUB\HC_AutoChat
set BK=D:\HCDecorHUB\HC_AutoChat\backups\pre-sync
if not exist "%DST%" mkdir "%DST%"
if exist "%DST%\src" xcopy "%DST%\src" "%BK%\src\" /E /I /Y >nul
xcopy "%SRC%\src" "%DST%\src\" /E /I /Y >nul
xcopy "%SRC%\tests" "%DST%\tests\" /E /I /Y >nul
xcopy "%SRC%\scripts" "%DST%\scripts\" /E /I /Y >nul
copy /Y "%SRC%\manifest.json" "%DST%\manifest.json" >nul
echo HC_AGENT_CONTROL_V1_SYNC_PASS
