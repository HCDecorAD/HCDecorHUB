@echo off
setlocal
cd /d %~dp0..
echo HC Agent Control watchdog. Ctrl+C to stop.
:loop
call scripts\92_WATCHDOG_ONCE.bat
timeout /t 30 /nobreak >nul
goto loop
