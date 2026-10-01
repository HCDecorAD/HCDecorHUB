@echo off
setlocal
cd /d %~dp0..
python scripts\release_readiness.py
exit /b %errorlevel%
