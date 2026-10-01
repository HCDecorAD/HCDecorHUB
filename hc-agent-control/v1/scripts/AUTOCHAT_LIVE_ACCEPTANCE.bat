@echo off
setlocal
cd /d %~dp0..
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 410
call scripts\11_CAPTURE_CP1_EVIDENCE.bat || exit /b 411
call scripts\12_VALIDATE_CP1_LIVE.bat || exit /b 412
call scripts\A1_A3_AUTOMATED_QA.bat || exit /b 413
call scripts\A4_A5_LIVE_QA.bat || exit /b 414
call scripts\A6_RESTART_ACCEPTANCE.bat || exit /b 415
call scripts\A7_PACKAGE_PUBLIC_RC.bat || exit /b 416
echo AUTOCHAT_LIVE_ACCEPTANCE_AUTOMATED_STAGES_PASS
exit /b 0
