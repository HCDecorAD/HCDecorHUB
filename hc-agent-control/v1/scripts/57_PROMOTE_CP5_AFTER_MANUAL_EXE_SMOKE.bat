@echo off
setlocal
cd /d %~dp0..
python scripts\validate_cp5_evidence.py || exit /b 112
python scripts\promote_gate.py CP5_PACKAGE_SMOKE checkpoints\CP5_PACKAGE_SMOKE.json
exit /b %errorlevel%
