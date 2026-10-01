@echo off
setlocal
cd /d %~dp0..
call scripts\00_PRECHECK.bat || exit /b 160
call scripts\08_MIGRATE_RUNTIME.bat || exit /b 161
call scripts\05_VALIDATE_PROJECT.bat || exit /b 162
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 163
call scripts\92_WATCHDOG_ONCE.bat || exit /b 164
call scripts\95_STATUS_REPORT.bat || exit /b 165
echo DAILY_USE_CHECK_PASS
echo Real Send state is unchanged.
exit /b 0
