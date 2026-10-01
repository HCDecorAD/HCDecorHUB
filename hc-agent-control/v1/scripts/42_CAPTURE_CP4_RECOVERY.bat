@echo off
setlocal
cd /d %~dp0..
python scripts\cp4_recovery_probe.py || exit /b 104
python scripts\write_checkpoint.py CP4_RECOVERY logs\cp4-recovery.json || exit /b 105
python scripts\promote_gate.py CP4_RECOVERY checkpoints\CP4_RECOVERY.json
exit /b %errorlevel%
