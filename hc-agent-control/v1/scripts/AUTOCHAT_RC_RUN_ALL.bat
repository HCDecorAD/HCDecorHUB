@echo off
setlocal
cd /d %~dp0..
call scripts\AUTOCHAT_STAGE_MATRIX.bat || exit /b 450
call scripts\AUTOCHAT_HOCUONG_PREFLIGHT.bat || exit /b 451
call scripts\A1_A3_AUTOMATED_QA.bat || exit /b 452
call scripts\A4_A5_LIVE_QA.bat || exit /b 453
call scripts\A6_RESTART_ACCEPTANCE.bat || exit /b 454
call scripts\A7_PACKAGE_PUBLIC_RC.bat || exit /b 455
call scripts\AUTOCHAT_PUBLIC_GATE.bat
exit /b %errorlevel%
