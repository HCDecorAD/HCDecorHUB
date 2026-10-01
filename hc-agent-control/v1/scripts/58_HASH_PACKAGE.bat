@echo off
setlocal
cd /d %~dp0..
python scripts\package_hashes.py dist\HC-Agent-Control-V1-Staging
exit /b %errorlevel%
