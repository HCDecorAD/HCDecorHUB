@echo off
setlocal
cd /d %~dp0..
if not exist logs mkdir logs
start "HCAC-UNIT" /b cmd /c "scripts\RUN_STAGING_TESTS.bat > logs\parallel-unit.log 2>&1 & echo %%errorlevel%% > logs\parallel-unit.exit"
start "HCAC-STRESS" /b cmd /c "scripts\40_STRESS_TEST.bat > logs\parallel-stress.log 2>&1 & echo %%errorlevel%% > logs\parallel-stress.exit"
start "HCAC-RECOVERY" /b cmd /c "scripts\41_RECOVERY_TEST.bat > logs\parallel-recovery.log 2>&1 & echo %%errorlevel%% > logs\parallel-recovery.exit"
start "HCAC-UI" /b cmd /c "scripts\31_TEST_UI.bat > logs\parallel-ui.log 2>&1 & echo %%errorlevel%% > logs\parallel-ui.exit"
echo PARALLEL_CHECKS_STARTED
echo Run scripts\WAIT_PARALLEL_CHECKS.bat to collect results.
