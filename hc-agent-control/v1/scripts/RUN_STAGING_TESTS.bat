@echo off
setlocal
cd /d %~dp0..
python -m unittest discover -s tests -p test_*.py
if errorlevel 1 exit /b 30
echo HC_AGENT_CONTROL_V1_STAGING_TESTS_PASS
exit /b 0
