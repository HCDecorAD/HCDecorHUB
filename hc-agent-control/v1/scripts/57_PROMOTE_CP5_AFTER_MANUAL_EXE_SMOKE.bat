@echo off
setlocal
cd /d %~dp0..
if not exist checkpoints\CP5_PACKAGE_SMOKE.json (echo CP5_CHECKPOINT_MISSING&exit /b 111)
echo This promotion confirms the packaged EXE was manually launched and visually smoke-tested on HOCUONG.
set /p OK=Type PASS to confirm: 
if /I not "%OK%"=="PASS" (echo CP5_PROMOTION_CANCELLED&exit /b 112)
python scripts\promote_gate.py CP5_PACKAGE_SMOKE checkpoints\CP5_PACKAGE_SMOKE.json
exit /b %errorlevel%
