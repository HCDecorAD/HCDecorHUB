@echo off
setlocal EnableDelayedExpansion
cd /d %~dp0..
for /f "tokens=1-4 delims=/ " %%a in ("%date%") do set DS=%%d%%b%%c
for /f "tokens=1-3 delims=:., " %%a in ("%time%") do set TS=%%a%%b%%c
set TS=%TS: =0%
set OUT=backups\snapshot-%DS%-%TS%
mkdir "%OUT%" || exit /b 60
for %%D in (src scripts tests docs data) do if exist "%%D" xcopy "%%D" "%OUT%\%%D\" /E /I /Y >nul
for %%F in (manifest.json release-gates.json HC_AutoChat.bat) do if exist "%%F" copy /Y "%%F" "%OUT%\" >nul
echo BACKUP_PASS %OUT%
exit /b 0
