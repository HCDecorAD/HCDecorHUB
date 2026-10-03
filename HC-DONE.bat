@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo ==================================================
echo HC DONE - CANONICAL PROJECT CLOSURE
echo ==================================================
echo [0/3] Preparing Local-First runtime on HOCUONG...
call "%~dp0HC-LOCAL-FIRST.bat" setup
if errorlevel 1 (
  echo HC_LOCAL_FIRST_BLOCKED rc=%ERRORLEVEL%
  exit /b %ERRORLEVEL%
)
echo [1/3] Running HC GROUP DONE with parallel Debug Green lanes...
call "%~dp0hc-group-done.bat" %*
if errorlevel 1 (
  echo HC_DONE_BLOCKED rc=%ERRORLEVEL%
  exit /b %ERRORLEVEL%
)
echo [2/3] Verifying canonical DONE package contracts...
call npm run test:hc-group-done-package
if errorlevel 1 exit /b %ERRORLEVEL%
call npm run test:remaining-done-plan
if errorlevel 1 exit /b %ERRORLEVEL%
echo [3/3] Local-First DONE confirmed. GitHub sync is non-blocking.
echo HC_DONE_CANONICAL_PASS
echo Evidence: .runtime\hc-group-done\summary.json
exit /b 0
