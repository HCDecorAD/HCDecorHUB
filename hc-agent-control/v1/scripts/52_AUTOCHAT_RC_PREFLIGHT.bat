@echo off
setlocal
cd /d %~dp0..
call scripts\51_AUTOCHAT_BAT_LINT.bat || exit /b 462
call scripts\AUTOCHAT_STAGE_MATRIX.bat || exit /b 463
call scripts\AUTOCHAT_SAFE_STOP.bat || exit /b 464
call scripts\AUTOCHAT_PUBLIC_STATUS.bat
if errorlevel 1 echo PUBLIC_STATUS_EXPECTED_BLOCKED_UNTIL_LIVE_EVIDENCE
exit /b 0
