@echo off
setlocal
cd /d %~dp0..
call scripts\A4_A5_PREFLIGHT.bat || exit /b 401
call scripts\A4_INPUT_TRANSPORT_QA.bat || exit /b 402
if "%AUTOCHAT_LIVE_CID%"=="" (echo LIVE_SEND_LOCKED exact CID required&exit /b 403)
if "%AUTOCHAT_LIVE_COMMAND%"=="" (echo LIVE_SEND_LOCKED exact command required&exit /b 404)
call scripts\A4_ARM_ONE.bat "%AUTOCHAT_LIVE_CID%" "%AUTOCHAT_LIVE_COMMAND%" || exit /b 405
python scripts\a4_live_once.py "%AUTOCHAT_LIVE_COMMAND%" || exit /b 406
echo AUTOCHAT_A4_A5_LIVE_QA_PASS
exit /b 0
