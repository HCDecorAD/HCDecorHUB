@echo off
setlocal
cd /d %~dp0..
call scripts\78_DAILY_BACKUP.bat || exit /b 222
call scripts\82_COMPACT_QUEUE.bat || exit /b 223
call scripts\91_LOG_CLEANUP.bat || exit /b 224
call scripts\83_HEALTH_SUMMARY.bat || exit /b 225
echo DAILY_MAINTENANCE_PASS
exit /b 0
