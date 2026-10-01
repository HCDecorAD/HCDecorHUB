@echo off
setlocal
cd /d %~dp0..
call scripts\00_PRECHECK.bat || exit /b 120
call scripts\01_BOOTSTRAP.bat || exit /b 121
call scripts\08_MIGRATE_RUNTIME.bat || exit /b 122
call scripts\80_RESET_SAFE_STATE.bat || exit /b 123
call scripts\05_VALIDATE_PROJECT.bat || exit /b 124
call scripts\07_SELFTEST_NO_BROWSER.bat || exit /b 125
echo FRESH_MACHINE_BOOTSTRAP_PASS
echo Next: scripts\03_ENSURE_MANAGED_EDGE.bat
exit /b 0
