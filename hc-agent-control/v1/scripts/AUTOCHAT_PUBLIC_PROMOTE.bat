@echo off
setlocal
cd /d %~dp0..
call scripts\AUTOCHAT_PUBLIC_GATE.bat || (
 echo PUBLIC_PROMOTION_DENIED
 exit /b 480
)
call scripts\AUTOCHAT_CHECKPOINT.bat
echo HC_AUTOCHAT_V1_PUBLIC_ACCEPTED
exit /b 0
