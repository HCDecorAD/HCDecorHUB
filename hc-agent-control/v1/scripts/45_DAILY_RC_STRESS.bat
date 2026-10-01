@echo off
setlocal
cd /d %~dp0..
call scripts\34_SETTINGS_STATUS_TEST.bat || exit /b 212
call scripts\44_SOAK_CORE.bat || exit /b 213
call scripts\54_PACKAGE_SAFETY_PROBE.bat || exit /b 214
call scripts\32_UI_RUNTIME_PROBE.bat || exit /b 215
echo DAILY_RC_STRESS_PASS
exit /b 0
