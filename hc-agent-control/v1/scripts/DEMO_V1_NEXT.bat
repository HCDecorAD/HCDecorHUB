@echo off
setlocal
cd /d %~dp0..
call scripts\07_SELFTEST_NO_BROWSER.bat || exit /b 150
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 151
python -m src.ui.app
exit /b %errorlevel%
