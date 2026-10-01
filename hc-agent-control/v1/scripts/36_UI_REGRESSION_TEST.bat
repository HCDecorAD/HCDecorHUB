@echo off
setlocal
cd /d %~dp0..
python -m unittest tests.test_ui_runtime_regressions_v1
exit /b %errorlevel%
