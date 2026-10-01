@echo off
setlocal EnableDelayedExpansion
cd /d %~dp0..
set FAIL=0
echo ===== HC AGENT CONTROL V1 PIPELINE =====
call :run "CP0 PRECHECK" scripts\00_PRECHECK.bat
call :run "CP0 BOOTSTRAP" scripts\01_BOOTSTRAP.bat
call :run "UNIT SUITE" scripts\RUN_STAGING_TESTS.bat
call :run "CP3 UI" scripts\31_TEST_UI.bat
call :run "STRESS" scripts\40_STRESS_TEST.bat
call :run "CP4 RECOVERY" scripts\41_RECOVERY_TEST.bat
call :run "STAGING PACKAGE" scripts\51_PACKAGE_STAGING.bat
call :run "SMOKE" scripts\52_SMOKE_TEST.bat
echo ===== PIPELINE SUMMARY =====
if !FAIL! NEQ 0 (echo STAGING_PIPELINE_FAIL count=!FAIL!&exit /b 70)
echo STAGING_PIPELINE_PASS
echo Production remains controlled by release-gates.json
exit /b 0
:run
echo.
echo --- %~1 ---
call %~2
if errorlevel 1 (echo FAIL %~1&set /a FAIL+=1) else echo PASS %~1
exit /b 0
