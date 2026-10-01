@echo off
setlocal
cd /d %~dp0..
call scripts\83_HEALTH_SUMMARY.bat
call scripts\95_STATUS_REPORT.bat
call scripts\75_EXPORT_QUEUE_HISTORY.bat
call scripts\97_EXPORT_DEBUG_BUNDLE.bat
echo SUPPORT_SNAPSHOT_COMPLETE
exit /b 0
