@echo off
setlocal
cd /d %~dp0..
echo ===== V1 NEXT PROMOTION PRECHECK =====
call scripts\62_BACKUP_UPGRADE_BASELINE.bat || exit /b 190
call scripts\07_SELFTEST_NO_BROWSER.bat || exit /b 191
call scripts\32_UI_RUNTIME_PROBE.bat || exit /b 192
call scripts\33_UI_POLICY_CHECK.bat || exit /b 193
call scripts\34_SETTINGS_STATUS_TEST.bat || exit /b 194
call scripts\45_DAILY_RC_STRESS.bat || exit /b 195
call scripts\76_DAILY_USE_RC_QA.bat || exit /b 196
call scripts\78_DAILY_BACKUP.bat || exit /b 197
echo PROMOTION_PRECHECK_PASS
echo This BAT does not merge branches and does not enable Real Send.
exit /b 0
