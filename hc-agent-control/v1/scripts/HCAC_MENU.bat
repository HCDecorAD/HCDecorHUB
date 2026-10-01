@echo off
setlocal
cd /d %~dp0..
:menu
cls
echo ========================================
echo   HC AGENT CONTROL - AUTOCHAT V1
echo ========================================
echo 1. Run UI
echo 2. Diagnostic
echo 3. Ensure Managed Edge
echo 4. Run Parallel Checks
echo 5. Collect Parallel Results
echo 6. Full Staging Pipeline
echo 7. Capture CP1 Live Evidence
echo 8. Validate/Promote CP1 Live
echo 9. Capture Alias Restart
echo B. Backup
echo R. Rollback Latest
echo S. STOP ALL Safe State
echo M. Maintenance
echo Q. Quit
set /p C=Choose: 
if /I "%C%"=="1" call scripts\30_RUN_DEV.bat
if /I "%C%"=="2" call scripts\90_DIAGNOSTIC.bat
if /I "%C%"=="3" call scripts\03_ENSURE_MANAGED_EDGE.bat
if /I "%C%"=="4" call scripts\RUN_PARALLEL_CHECKS.bat
if /I "%C%"=="5" call scripts\WAIT_PARALLEL_CHECKS.bat
if /I "%C%"=="6" call scripts\RUN_V1_PIPELINE.bat
if /I "%C%"=="7" call scripts\11_CAPTURE_CP1_EVIDENCE.bat
if /I "%C%"=="8" call scripts\13_PROMOTE_CP1_LIVE.bat
if /I "%C%"=="9" call scripts\14_CAPTURE_ALIAS_RESTART.bat
if /I "%C%"=="B" call scripts\60_BACKUP_RELEASE.bat
if /I "%C%"=="R" call scripts\61_ROLLBACK_LATEST.bat
if /I "%C%"=="S" call scripts\80_RESET_SAFE_STATE.bat
if /I "%C%"=="M" call scripts\94_MAINTENANCE.bat
if /I "%C%"=="Q" exit /b 0
echo.
pause
goto menu
