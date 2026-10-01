@echo off
setlocal
cd /d %~dp0..
call scripts\64_BACKUP_USER_STATE.bat || exit /b 178
call scripts\75_EXPORT_QUEUE_HISTORY.bat || exit /b 179
call scripts\95_STATUS_REPORT.bat || exit /b 180
echo DAILY_BACKUP_PASS
exit /b 0
