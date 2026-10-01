@echo off
setlocal
cd /d %~dp0..
python -m unittest tests.test_cdp_readonly_v1 tests.test_activity_v1
exit /b %errorlevel%
