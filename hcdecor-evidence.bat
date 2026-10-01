@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HCDecor Release Evidence ===
git rev-parse HEAD > .hcdecor-release-sha.tmp || goto FAIL
set /p SHA=<.hcdecor-release-sha.tmp
del .hcdecor-release-sha.tmp >nul 2>nul
echo Source SHA: %SHA%
call hcdecor-public-candidate.bat
if errorlevel 1 goto FAIL
echo.
echo RELEASE_EVIDENCE_SHA=%SHA%
echo RELEASE_EVIDENCE_STATE=CANDIDATE_CERTIFIED_LOCKED
echo Production authority must remain explicitly bound and approved before mutation.
exit /b 0
:FAIL
del .hcdecor-release-sha.tmp >nul 2>nul
echo HCDECOR_RELEASE_EVIDENCE_FAIL
exit /b 1
