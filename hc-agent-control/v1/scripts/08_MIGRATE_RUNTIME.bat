@echo off
setlocal
cd /d %~dp0..
if not exist backups\migrations mkdir backups\migrations
python scripts\migrate_runtime.py
exit /b %errorlevel%
