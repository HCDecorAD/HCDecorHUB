@echo off
setlocal
cd /d %~dp0..
call scripts\11_CAPTURE_CP1_EVIDENCE.bat || exit /b 21
python scripts\capture_alias_restart.py || exit /b %errorlevel%
python scripts\write_checkpoint.py CP1_ALIAS_RESTART logs\cp1-alias-restart.json || exit /b 99
python scripts\promote_gate.py CP1_ALIAS_RESTART checkpoints\CP1_ALIAS_RESTART.json
exit /b %errorlevel%
