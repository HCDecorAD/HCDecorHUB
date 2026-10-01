@echo off
setlocal
cd /d %~dp0..
call scripts\64_BACKUP_USER_STATE.bat || exit /b 65
call scripts\60_BACKUP_RELEASE.bat || exit /b 66
call scripts\80_RESET_SAFE_STATE.bat || exit /b 67
echo UPDATE_PREPARED safe-state=STOP_ALL
exit /b 0
