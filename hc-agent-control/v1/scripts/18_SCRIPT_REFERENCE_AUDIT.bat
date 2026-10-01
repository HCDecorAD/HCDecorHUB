@echo off
setlocal
cd /d %~dp0..
python scripts\script_reference_audit.py
exit /b %errorlevel%
