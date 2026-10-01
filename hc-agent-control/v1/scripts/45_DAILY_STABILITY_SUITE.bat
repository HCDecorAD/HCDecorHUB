@echo off
setlocal
cd /d %~dp0..
call scripts\32_UI_RUNTIME_PROBE.bat || exit /b 210
call scripts\34_SETTINGS_STATUS_TEST.bat || exit /b 211
call scripts\40_STRESS_TEST.bat || exit /b 212
call scripts\44_SOAK_TEST.bat || exit /b 213
call scripts\54_PACKAGE_SAFETY_PROBE.bat || exit /b 214
echo DAILY_STABILITY_SUITE_PASS
exit /b 0
