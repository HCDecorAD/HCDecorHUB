@echo off
setlocal
cd /d %~dp0..
call scripts\73_DAILY_USE_CHECK.bat || exit /b 250
call scripts\86_ACCEPTANCE_MANIFEST.bat || exit /b 251
start "" cmd /c "scripts\30_RUN_DEV.bat"
echo DEMO_LAUNCHED
echo Review aliases, queue controls, STOP ALL, theme, auto refresh and connection recovery.
exit /b 0
