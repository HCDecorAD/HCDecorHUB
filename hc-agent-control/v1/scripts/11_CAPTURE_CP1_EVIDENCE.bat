@echo off
setlocal
cd /d %~dp0..
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 19
python src\core\cp1_probe.py || exit /b 21
python scripts\write_checkpoint.py CP1_AUTOMATED logs\cp1-real-cdp.json
exit /b %errorlevel%
