@echo off
setlocal
cd /d %~dp0..
python -m unittest tests.test_cdp_v1 tests.test_verifier_v1 tests.test_resolver_v1 tests.test_service_v1
if errorlevel 1 exit /b 20
python src\core\cp1_probe.py
if errorlevel 1 exit /b 21
echo CP1_AUTOMATED_CHECKS_PASS
exit /b 0
