@echo off
setlocal
cd /d %~dp0..
call scripts\00_PRECHECK.bat || exit /b 30
call scripts\01_BOOTSTRAP.bat || exit /b 31
python -m src.ui.app
exit /b %errorlevel%
