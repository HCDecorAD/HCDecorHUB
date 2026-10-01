@echo off
setlocal
cd /d %~dp0..
call scripts\A4_CHECK_TRANSPORT.bat || exit /b 395
python -m unittest tests.test_cdp_input_policy_v1 tests.test_exact_adapter_v1 tests.test_postverify_v1 || exit /b 396
echo A4_INPUT_TRANSPORT_QA_PASS
exit /b 0
