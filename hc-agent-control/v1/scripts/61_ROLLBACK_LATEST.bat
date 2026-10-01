@echo off
setlocal
cd /d %~dp0..
set LATEST=
for /f "delims=" %%D in ('dir /b /ad /o-d backups\snapshot-* 2^>nul') do if not defined LATEST set LATEST=%%D
if not defined LATEST (echo NO_BACKUP_FOUND&exit /b 61)
echo ROLLBACK_SOURCE backups\%LATEST%
xcopy "backups\%LATEST%\src" "src\" /E /I /Y >nul || exit /b 62
xcopy "backups\%LATEST%\scripts" "scripts\" /E /I /Y >nul
xcopy "backups\%LATEST%\tests" "tests\" /E /I /Y >nul
for %%F in (manifest.json release-gates.json HC_AutoChat.bat) do if exist "backups\%LATEST%\%%F" copy /Y "backups\%LATEST%\%%F" "%%F" >nul
echo ROLLBACK_PASS %LATEST%
exit /b 0
