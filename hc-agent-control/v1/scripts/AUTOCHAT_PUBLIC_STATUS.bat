@echo off
setlocal
cd /d %~dp0..
python scripts\autochat_public_status.py
exit /b %errorlevel%
