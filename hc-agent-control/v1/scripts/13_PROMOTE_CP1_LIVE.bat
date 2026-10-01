@echo off
setlocal
cd /d %~dp0..
call scripts\12_VALIDATE_CP1_LIVE.bat || exit /b 95
python scripts\promote_gate.py CP1_LIVE_MULTI_CHAT checkpoints\CP1_LIVE_MULTI_CHAT.json
exit /b %errorlevel%
