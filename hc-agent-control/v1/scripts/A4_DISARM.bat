@echo off
setlocal
cd /d %~dp0..
if exist runtime\G2_LIVE_ARM.json del /q runtime\G2_LIVE_ARM.json
echo AUTOCHAT_SEND_DISARMED
exit /b 0
