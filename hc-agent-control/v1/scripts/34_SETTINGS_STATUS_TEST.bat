@echo off
setlocal
cd /d %~dp0..
python -m unittest tests.test_settings_v1 tests.test_heartbeat_v1 tests.test_live_status_v1
exit /b %errorlevel%
