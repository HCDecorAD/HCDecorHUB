@echo off
setlocal
cd /d %~dp0..
python -m unittest tests.test_ui_policy_v1 tests.test_daily_ui_policy_v1 tests.test_first_run_policy_v1
exit /b %errorlevel%
