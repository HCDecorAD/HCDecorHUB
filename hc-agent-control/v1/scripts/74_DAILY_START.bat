@echo off
setlocal
cd /d %~dp0..
call scripts\73_DAILY_USE_CHECK.bat || (echo DAILY_START_BLOCKED&pause&exit /b 166)
call HC_AutoChat.bat
