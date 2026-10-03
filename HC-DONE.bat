@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo ==================================================
echo HC DONE - CANONICAL PROJECT CLOSURE
echo ==================================================
echo [1/2] Running HC GROUP DONE with parallel Debug Green lanes...
call "%~dp0hc-group-done.bat" %*
if errorlevel 1 (
  echo HC_DONE_BLOCKED rc=%ERRORLEVEL%
  exit /b %ERRORLEVEL%
)
echo [2/2] Verifying canonical DONE package contracts...
call npm run test:hc-group-done-package
if errorlevel 1 exit /b %ERRORLEVEL%
call npm run test:remaining-done-plan
if errorlevel 1 exit /b %ERRORLEVEL%
echo HC_DONE_CANONICAL_PASS
echo Evidence: .runtime\hc-group-done\summary.json
exit /b 0
