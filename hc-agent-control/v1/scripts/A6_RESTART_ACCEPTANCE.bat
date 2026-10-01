@echo off
setlocal
cd /d %~dp0..
call scripts\15_CAPTURE_RESTART_PRE.bat || exit /b 380
call scripts\43_CP4_MANAGED_EDGE_RESTART_TEST.bat || exit /b 381
call scripts\11_CAPTURE_CP1_EVIDENCE.bat || exit /b 382
call scripts\16_CAPTURE_RESTART_POST.bat || exit /b 383
echo AUTOCHAT_A6_RESTART_ACCEPTANCE_PASS
exit /b 0
