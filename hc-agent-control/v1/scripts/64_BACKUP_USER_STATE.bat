@echo off
setlocal
cd /d %~dp0..
python scripts\backup_manager.py user
exit /b %errorlevel%
