@echo off
setlocal EnableDelayedExpansion
cd /d %~dp0..
for /f "tokens=2 delims==" %%A in ('wmic os get localdatetime /value 2^>nul ^| find "="') do set DT=%%A
if not defined DT set DT=manual
set OUT=backups\user-state-%DT:~0,14%
mkdir "%OUT%" || exit /b 64
if exist data xcopy data "%OUT%\data\" /E /I /Y >nul
if exist logs\status-report.json copy /Y logs\status-report.json "%OUT%\" >nul
echo USER_STATE_BACKUP_PASS %OUT%
exit /b 0
