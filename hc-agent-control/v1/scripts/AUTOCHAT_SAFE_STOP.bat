@echo off
setlocal
cd /d %~dp0..
if exist runtime\G2_LIVE_ARM.json del /q runtime\G2_LIVE_ARM.json
if exist runtime\STOP_ALL.flag del /q runtime\STOP_ALL.flag
> runtime\STOP_ALL.flag echo STOP_ALL
python scripts\stop_all_evidence.py || exit /b 1
echo AUTOCHAT_SAFE_STOP: all live send disarmed
exit /b 0
