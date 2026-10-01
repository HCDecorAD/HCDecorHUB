@echo off
setlocal
cd /d %~dp0..
call scripts\68_EARLY_USE_ACCEPTANCE.bat || exit /b 90
set TARGET=D:\HCDecorHUB\HC Agent Control V1 EarlyUse
if exist "%TARGET%" (
 echo TARGET_EXISTS %TARGET%
 echo Existing user data will not be overwritten.
 xcopy dist\HC-Agent-Control-V1-EarlyUse\src "%TARGET%\src\" /E /I /Y >nul
 xcopy dist\HC-Agent-Control-V1-EarlyUse\scripts "%TARGET%\scripts\" /E /I /Y >nul
 xcopy dist\HC-Agent-Control-V1-EarlyUse\docs "%TARGET%\docs\" /E /I /Y >nul
 copy /Y dist\HC-Agent-Control-V1-EarlyUse\HC_AutoChat.bat "%TARGET%\" >nul
 copy /Y dist\HC-Agent-Control-V1-EarlyUse\START_HC_AGENT_CONTROL.bat "%TARGET%\" >nul
 copy /Y dist\HC-Agent-Control-V1-EarlyUse\CONTROL_MENU.bat "%TARGET%\" >nul
) else (
 xcopy dist\HC-Agent-Control-V1-EarlyUse "%TARGET%\" /E /I /Y >nul
)
echo EARLY_USE_LOCAL_DEPLOY_READY %TARGET%
echo Run START_HC_AGENT_CONTROL.bat on HOCUONG.
exit /b 0
