@echo off
setlocal
cd /d %~dp0..
python scripts\ui_runtime_probe.py
exit /b %errorlevel%
