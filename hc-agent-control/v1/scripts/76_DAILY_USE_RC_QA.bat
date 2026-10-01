@echo off
setlocal
cd /d %~dp0..
call scripts\07_SELFTEST_NO_BROWSER.bat || exit /b 170
call scripts\73_DAILY_USE_CHECK.bat || exit /b 171
call scripts\75_EXPORT_QUEUE_HISTORY.bat || exit /b 172
call scripts\51_PACKAGE_STAGING.bat || exit /b 173
call scripts\53_PORTABLE_SMOKE.bat || exit /b 174
call scripts\58_HASH_PACKAGE.bat || exit /b 175
echo DAILY_USE_RC_QA_PASS
echo Scope remains SEND OFF.
exit /b 0
