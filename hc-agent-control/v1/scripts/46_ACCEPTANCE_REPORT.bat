@echo off
setlocal
cd /d %~dp0..
python scripts\acceptance_report.py
exit /b %errorlevel%
