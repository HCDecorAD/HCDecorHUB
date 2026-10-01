@echo off
setlocal
cd /d %~dp0..
call scripts\51_PACKAGE_STAGING.bat || exit /b 200
python scripts\package_safety_probe.py
exit /b %errorlevel%
