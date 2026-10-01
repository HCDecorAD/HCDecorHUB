@echo off
setlocal
cd /d %~dp0..
if not exist logs\acceptance-report.json (echo ACCEPTANCE_REPORT_MISSING&exit /b 228)
start "" notepad.exe logs\acceptance-report.json
exit /b 0
