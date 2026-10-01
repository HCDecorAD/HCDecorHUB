@echo off
setlocal
cd /d %~dp0..
echo This test restarts only Edge processes whose command line contains this HCAC edge-profile.
powershell -NoProfile -Command "$p=(Resolve-Path 'runtime\edge-profile').Path; Get-CimInstance Win32_Process -Filter \"Name='msedge.exe'\" | Where-Object {$_.CommandLine -like ('*'+$p+'*')} | ForEach-Object {Stop-Process -Id $_.ProcessId -Force}" || exit /b 106
timeout /t 2 /nobreak >nul
python scripts\cp4_recovery_probe.py --require-down
exit /b %errorlevel%
