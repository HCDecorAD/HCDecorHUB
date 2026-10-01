@echo off
setlocal
cd /d %~dp0..
python scripts\validate_project.py
exit /b %errorlevel%
