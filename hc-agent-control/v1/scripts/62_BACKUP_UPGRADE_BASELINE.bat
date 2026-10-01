@echo off
setlocal EnableDelayedExpansion
cd /d %~dp0..
for /f "tokens=2 delims==" %%A in ('wmic os get localdatetime /value 2^>nul ^| find "="') do set DT=%%A
if not defined DT set DT=%date:~-4%%date:~4,2%%date:~7,2%-%time:~0,2%%time:~3,2%%time:~6,2%
set DT=%DT: =0%
set OUT=backups\upgrade-baseline-%DT:~0,8%-%DT:~8,6%
mkdir "%OUT%" || exit /b 62
for %%D in (src scripts tests docs) do if exist "%%D" xcopy "%%D" "%OUT%\%%D\" /E /I /Y >nul
for %%F in (manifest.json release-gates.json HC_AutoChat.bat) do if exist "%%F" copy /Y "%%F" "%OUT%\" >nul
> "%OUT%\UPGRADE-BASELINE.txt" echo HC Agent Control V1 early-use baseline - preserve for future V2/V3 upgrades.
echo UPGRADE_BASELINE_BACKUP_PASS %OUT%
exit /b 0
