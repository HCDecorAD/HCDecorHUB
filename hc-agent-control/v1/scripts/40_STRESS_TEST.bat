@echo off
setlocal
cd /d %~dp0..
python scripts\stress_queue.py
if errorlevel 1 exit /b 40
echo STRESS_TEST_PASS
exit /b 0
