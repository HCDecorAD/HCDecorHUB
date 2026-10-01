@echo off
setlocal
cd /d %~dp0..
python -m unittest tests.test_recovery_v1 tests.test_service_v1 tests.test_resolver_v1
if errorlevel 1 exit /b 40
echo CP4_AUTOMATED_RECOVERY_PASS
exit /b 0
