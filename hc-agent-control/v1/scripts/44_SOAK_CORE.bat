@echo off
setlocal
cd /d %~dp0..
python scripts\soak_core.py
exit /b %errorlevel%
