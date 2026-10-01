@echo off
setlocal
cd /d %~dp0..
if not exist backups mkdir backups
python scripts\repair_queue.py
exit /b %errorlevel%
