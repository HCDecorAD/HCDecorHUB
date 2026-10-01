@echo off
setlocal
cd /d %~dp0..
python scripts\health_summary.py
exit /b %errorlevel%
