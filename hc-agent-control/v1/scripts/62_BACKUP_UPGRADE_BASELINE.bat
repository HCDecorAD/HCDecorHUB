@echo off
setlocal
cd /d %~dp0..
python scripts\backup_manager.py upgrade
exit /b %errorlevel%
