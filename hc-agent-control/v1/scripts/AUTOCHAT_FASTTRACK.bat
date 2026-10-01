@echo off
setlocal
cd /d %~dp0..
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 341
python scripts\autochat_fasttrack.py
exit /b %errorlevel%
