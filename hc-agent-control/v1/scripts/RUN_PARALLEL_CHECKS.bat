@echo off
setlocal
cd /d %~dp0..
python scripts\parallel_checks.py
exit /b %errorlevel%
