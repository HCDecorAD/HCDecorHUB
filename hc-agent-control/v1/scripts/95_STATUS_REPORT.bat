@echo off
setlocal
cd /d %~dp0..
python scripts\status_report.py
exit /b %errorlevel%
