@echo off
setlocal
cd /d %~dp0..
python scripts\validate_cp1_live.py || exit /b %errorlevel%
python scripts\write_checkpoint.py CP1_LIVE_MULTI_CHAT logs\cp1-real-cdp.json
echo CP1_LIVE_MULTI_CHAT_VALIDATED
