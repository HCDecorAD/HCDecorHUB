@echo off
setlocal
cd /d %~dp0..
call scripts\68_EARLY_USE_ACCEPTANCE.bat || exit /b 90
call scripts\64_BACKUP_USER_STATE.bat || exit /b 91
call scripts\80_RESET_SAFE_STATE.bat || exit /b 92
set TARGET=D:\HCDecorHUB\HC Agent Control V1 EarlyUse
if exist "%TARGET%" (
 echo TARGET_EXISTS %TARGET%
 xcopy dist\HC-Agent-Control-V1-EarlyUse\src "%TARGET%\src\" /E /I /Y >nul || exit /b 93
 xcopy dist\HC-Agent-Control-V1-EarlyUse\scripts "%TARGET%\scripts\" /E /I /Y >nul || exit /b 94
 xcopy dist\HC-Agent-Control-V1-EarlyUse\docs "%TARGET%\docs\" /E /I /Y >nul || exit /b 95
 copy /Y dist\HC-Agent-Control-V1-EarlyUse\HC_AutoChat.bat "%TARGET%\" >nul || exit /b 96
 copy /Y dist\HC-Agent-Control-V1-EarlyUse\START_HC_AGENT_CONTROL.bat "%TARGET%\" >nul || exit /b 97
 copy /Y dist\HC-Agent-Control-V1-EarlyUse\CONTROL_MENU.bat "%TARGET%\" >nul || exit /b 98
) else (
 xcopy dist\HC-Agent-Control-V1-EarlyUse "%TARGET%\" /E /I /Y >nul || exit /b 99
)
pushd "%TARGET%" || exit /b 100
call scripts\08_MIGRATE_RUNTIME.bat || (popd&exit /b 101)
call scripts\07_SELFTEST_NO_BROWSER.bat || (popd&exit /b 102)
call scripts\80_RESET_SAFE_STATE.bat || (popd&exit /b 103)
popd
echo EARLY_USE_LOCAL_DEPLOY_PASS %TARGET%
exit /b 0
