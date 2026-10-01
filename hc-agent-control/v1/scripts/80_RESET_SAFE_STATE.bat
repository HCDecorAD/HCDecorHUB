@echo off
setlocal
cd /d %~dp0..
python scripts\reset_safe_state.py
exit /b %errorlevel%
