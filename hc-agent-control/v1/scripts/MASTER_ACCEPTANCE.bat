@echo off
setlocal EnableDelayedExpansion
cd /d %~dp0..
echo ===== HC AGENT CONTROL V1 MASTER ACCEPTANCE =====
call scripts\09_FRESH_MACHINE_BOOTSTRAP.bat || exit /b 130
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 131
call scripts\RUN_V1_PIPELINE.bat || exit /b 132
call scripts\12_VALIDATE_CP1_LIVE.bat || (echo LIVE_CP1_STILL_OPEN&exit /b 133)
call scripts\14_CAPTURE_ALIAS_RESTART.bat || (echo ALIAS_RESTART_STILL_OPEN&exit /b 134)
call scripts\42_CAPTURE_CP4_RECOVERY.bat || exit /b 135
call scripts\56_CP5_ACCEPTANCE.bat || exit /b 136
python scripts\CHECK_RELEASE_GATE.py
if errorlevel 1 (echo MASTER_ACCEPTANCE_BLOCKED_BY_REMAINING_GATES&exit /b 137)
echo V1_MASTER_ACCEPTANCE_PASS
exit /b 0
