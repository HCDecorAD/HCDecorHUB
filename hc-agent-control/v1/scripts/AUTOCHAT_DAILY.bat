@echo off
setlocal
cd /d %~dp0..
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 351
call scripts\08_MIGRATE_RUNTIME.bat || exit /b 352
call scripts\80_RESET_SAFE_STATE.bat || exit /b 353
call scripts\30_RUN_DEV.bat
exit /b %errorlevel%
