@echo off
setlocal
cd /d %~dp0..
call scripts\61_ROLLBACK_LATEST.bat || exit /b 71
call scripts\08_MIGRATE_RUNTIME.bat || exit /b 72
call scripts\80_RESET_SAFE_STATE.bat || exit /b 73
echo UPDATE_ROLLBACK_PASS
exit /b 0
