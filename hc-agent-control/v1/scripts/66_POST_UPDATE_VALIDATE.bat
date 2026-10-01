@echo off
setlocal
cd /d %~dp0..
call scripts\08_MIGRATE_RUNTIME.bat || exit /b 68
call scripts\07_SELFTEST_NO_BROWSER.bat || exit /b 69
call scripts\95_STATUS_REPORT.bat || exit /b 70
echo POST_UPDATE_VALIDATE_PASS
exit /b 0
