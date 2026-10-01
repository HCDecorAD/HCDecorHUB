@echo off
setlocal
cd /d %~dp0..
python scripts\soak_test.py
exit /b %errorlevel%
