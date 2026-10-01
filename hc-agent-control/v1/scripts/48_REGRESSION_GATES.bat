@echo off
setlocal
cd /d %~dp0..
python -m unittest tests.test_recovery_zero_attempt_v1 tests.test_backup_policy_v1 tests.test_parallel_runner_policy_v1 tests.test_cp5_no_manual_pass_v1 tests.test_strong_gate_policy_v1 tests.test_gate_audit_policy_v1
if errorlevel 1 exit /b 311
call scripts\17_GATE_AUDIT.bat || exit /b 312
echo REGRESSION_GATES_PASS
exit /b 0
