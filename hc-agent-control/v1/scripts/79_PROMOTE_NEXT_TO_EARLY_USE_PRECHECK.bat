@echo off
setlocal
cd /d %~dp0..
echo ===== V1 NEXT PROMOTION PRECHECK =====
call scripts\62_BACKUP_UPGRADE_BASELINE.bat || exit /b 190
call scripts\07_SELFTEST_NO_BROWSER.bat || exit /b 191
call scripts\32_UI_RUNTIME_PROBE.bat || exit /b 192
call scripts\76_DAILY_USE_RC_QA.bat || exit /b 193
call scripts\78_DAILY_BACKUP.bat || exit /b 194
echo PROMOTION_PRECHECK_PASS
echo This BAT does not merge branches and does not enable Real Send.
exit /b 0
