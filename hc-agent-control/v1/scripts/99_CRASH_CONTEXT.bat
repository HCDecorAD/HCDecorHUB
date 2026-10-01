@echo off
setlocal
cd /d %~dp0..
python scripts\crash_report.py
call scripts\95_STATUS_REPORT.bat
echo CRASH_CONTEXT_PASS
