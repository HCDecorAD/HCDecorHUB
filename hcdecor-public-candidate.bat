@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HCDecor Public Candidate Certification ===
call hcdecor-prepublic.bat
if errorlevel 1 goto FAIL
call hcdecor-release-certify.bat
if errorlevel 1 goto FAIL
echo.
echo HCDECOR_PUBLIC_CANDIDATE_CERTIFIED_LOCKED
echo Candidate certification does not grant production mutation authority.
exit /b 0
:FAIL
echo HCDECOR_PUBLIC_CANDIDATE_FAILED
exit /b 1
