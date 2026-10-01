@echo off
setlocal
cd /d %~dp0..
call scripts\63_FREEZE_EARLY_USE.bat || exit /b 59
set OUT=dist\HC-Agent-Control-V1-EarlyUse
if exist "%OUT%" rmdir /s /q "%OUT%"
mkdir "%OUT%" || exit /b 60
xcopy src "%OUT%\src\" /E /I /Y >nul || exit /b 61
xcopy scripts "%OUT%\scripts\" /E /I /Y >nul || exit /b 62
xcopy docs "%OUT%\docs\" /E /I /Y >nul 2>nul
mkdir "%OUT%\data" 2>nul
mkdir "%OUT%\logs" 2>nul
mkdir "%OUT%\runtime" 2>nul
mkdir "%OUT%\backups" 2>nul
> "%OUT%\data\chats.json" echo {"version":1,"chats":{}}
> "%OUT%\data\queue.json" echo {"items":[],"global_paused":true,"paused_aliases":[]}
copy /Y HC_AutoChat.bat "%OUT%\" >nul
copy /Y manifest.json "%OUT%\" >nul
copy /Y release-gates.json "%OUT%\" >nul
> "%OUT%\START_HC_AGENT_CONTROL.bat" echo @echo off
>>"%OUT%\START_HC_AGENT_CONTROL.bat" echo cd /d "%%~dp0"
>>"%OUT%\START_HC_AGENT_CONTROL.bat" echo call scripts\09_FRESH_MACHINE_BOOTSTRAP.bat
>>"%OUT%\START_HC_AGENT_CONTROL.bat" echo call HC_AutoChat.bat
> "%OUT%\CONTROL_MENU.bat" echo @echo off
>>"%OUT%\CONTROL_MENU.bat" echo cd /d "%%~dp0"
>>"%OUT%\CONTROL_MENU.bat" echo call scripts\HCAC_MENU.bat
if exist "logs\a5-live-verify.json" ( > "%OUT%\EARLY-USE.txt" echo EARLY USE BUILD - live send acceptance evidence present; runtime still fail-closed by one-shot arming. ) else ( > "%OUT%\EARLY-USE.txt" echo EARLY USE SAFE BUILD - live send acceptance evidence not present. )
python scripts\package_hashes.py "%OUT%" || exit /b 66
echo EARLY_USE_PACKAGE_PASS %OUT%
exit /b 0
