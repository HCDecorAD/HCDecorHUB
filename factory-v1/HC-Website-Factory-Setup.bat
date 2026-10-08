@echo off
setlocal EnableExtensions
set "ROOT=D:\HCDecorHUB\HC Website Factory"
if not exist "D:\HCDecorHUB" (echo [ERROR] Root missing & exit /b 1)
for %%D in ("00-Docs" "01-WordPress-Core" "02-Themes" "03-Plugins" "04-Golden-Templates" "05-Client-Projects\AMO" "05-Client-Projects\GSC" "06-Customer-Admin" "07-Backup-Restore" "08-Deploy-Publish" "09-QA-Reports" "10-Downloads" "11-Scripts") do if not exist "%ROOT%\%%~D" mkdir "%ROOT%\%%~D"
echo [DONE] Folders created. No production changes.
endlocal
