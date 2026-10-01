@echo off
setlocal
cd /d %~dp0..
call scripts\05_VALIDATE_PROJECT.bat || exit /b 71
call scripts\06_VALIDATE_BAT_REFERENCES.bat || exit /b 72
call scripts\RUN_STAGING_TESTS.bat || exit /b 73
call scripts\40_STRESS_TEST.bat || exit /b 74
call scripts\31_TEST_UI.bat || exit /b 75
echo SELFTEST_NO_BROWSER_PASS
exit /b 0
