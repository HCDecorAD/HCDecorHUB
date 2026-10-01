@echo off
setlocal
cd /d %~dp0..
python scripts\gate_audit.py
exit /b %errorlevel%
