@echo off
setlocal
cd /d %~dp0..
echo HC Agent Control SAFE overnight loop. No ChatGPT send operations are called.
:loop
echo [%date% %time%] cycle start>>logs\overnight.log
call scripts\07_SELFTEST_NO_BROWSER.bat >>logs\overnight.log 2>&1
call scripts\51_PACKAGE_STAGING.bat >>logs\overnight.log 2>&1
call scripts\91_LOG_CLEANUP.bat >>logs\overnight.log 2>&1
call scripts\95_STATUS_REPORT.bat >>logs\overnight.log 2>&1
echo [%date% %time%] cycle end>>logs\overnight.log
timeout /t 1800 /nobreak >nul
goto loop
