@echo off
setlocal
cd /d %~dp0..
python -m unittest tests.test_verifier_v1 tests.test_queue_v1 tests.test_dispatcher_v1 tests.test_resolver_v1
if errorlevel 1 exit /b 20
echo CP2_DRY_RUN_PASS
exit /b 0
