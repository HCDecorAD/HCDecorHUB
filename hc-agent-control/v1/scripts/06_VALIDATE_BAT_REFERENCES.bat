@echo off
setlocal
cd /d %~dp0..
python scripts\inventory.py
exit /b %errorlevel%
