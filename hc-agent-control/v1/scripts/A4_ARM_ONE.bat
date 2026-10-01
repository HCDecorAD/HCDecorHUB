@echo off
setlocal
cd /d %~dp0..
if "%~1"=="" (echo Usage: A4_ARM_ONE.bat CONVERSATION_ID&exit /b 368)
call scripts\12_VALIDATE_CP1_LIVE.bat || exit /b 369
call scripts\A4_CHECK_TRANSPORT.bat || exit /b 370
python scripts\a4_dom_probe.py "%~1" || exit /b 371
python scripts\autochat_arm_once.py "%~1"
echo ONE COMMAND ONLY. Exact send implementation must consume and delete this token.
exit /b %errorlevel%
