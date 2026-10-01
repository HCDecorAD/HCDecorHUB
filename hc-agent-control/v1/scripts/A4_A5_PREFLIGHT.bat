@echo off
setlocal
cd /d %~dp0..
call scripts\A4_CHECK_TRANSPORT.bat || exit /b 361
python -m unittest tests.test_exact_adapter_v1 tests.test_dispatcher_v1 tests.test_postverify_v1 tests.test_arming_v1 || exit /b 362
echo A4_A5_PREFLIGHT_PASS_SEND_STILL_OFF
exit /b 0
