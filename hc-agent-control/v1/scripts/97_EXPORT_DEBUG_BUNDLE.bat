@echo off
setlocal
cd /d %~dp0..
if not exist dist mkdir dist
python scripts\export_debug_bundle.py
exit /b %errorlevel%
