@echo off
setlocal
cd /d %~dp0..
call scripts\32_UI_RUNTIME_PROBE.bat || exit /b 221
call scripts\33_UI_POLICY_CHECK.bat || exit /b 222
call scripts\34_SETTINGS_STATUS_TEST.bat || exit /b 223
call scripts\35_CDP_READONLY_POLICY.bat || exit /b 224
call scripts\45_DAILY_RC_STRESS.bat || exit /b 225
call scripts\54_PACKAGE_SAFETY_PROBE.bat || exit /b 226
call scripts\46_ACCEPTANCE_REPORT.bat || exit /b 227
echo RC_ACCEPTANCE_ALL_PASS
echo Real Send remains OFF.
exit /b 0
