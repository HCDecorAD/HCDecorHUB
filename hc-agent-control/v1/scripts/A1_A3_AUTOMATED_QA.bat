@echo off
setlocal
cd /d %~dp0..
call scripts\47_STATIC_COMPILE_ALL.bat || exit /b 343
python -m unittest tests.test_exact_adapter_v1 tests.test_registry_v1 tests.test_resolver_v1 tests.test_live_status_v1 || exit /b 344
call scripts\32_UI_RUNTIME_PROBE.bat || exit /b 345
echo AUTOCHAT_A1_A3_AUTOMATED_QA_PASS
exit /b 0
