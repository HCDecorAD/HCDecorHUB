@echo off
setlocal
cd /d %~dp0..
call scripts\91_LOG_CLEANUP.bat
call scripts\90_DIAGNOSTIC.bat
call scripts\05_VALIDATE_PROJECT.bat
echo MAINTENANCE_DONE
