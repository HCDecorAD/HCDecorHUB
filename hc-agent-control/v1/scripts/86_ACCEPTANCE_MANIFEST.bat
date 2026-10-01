@echo off
setlocal
cd /d %~dp0..
python scripts\acceptance_manifest.py
exit /b %errorlevel%
