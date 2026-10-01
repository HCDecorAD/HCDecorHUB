@echo off
setlocal
cd /d %~dp0..
python scripts\autochat_public_status.py
if errorlevel 1 (
 echo AUTOCHAT_PUBLIC_BLOCKED_EVIDENCE_INCOMPLETE
 exit /b 420
)
echo AUTOCHAT_PUBLIC_GATE_PASS
exit /b 0
