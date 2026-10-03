@echo off
setlocal EnableExtensions
cd /d "%~dp0"

set MODE=%~1
if "%MODE%"=="" set MODE=status

echo ==================================================
echo HC LOCAL-FIRST - HOCUONG LAPTOP
echo ==================================================

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\hc-local-first.ps1" -Mode "%MODE%"
set RC=%ERRORLEVEL%

if not "%RC%"=="0" (
  echo LOCAL_FIRST_FAILED rc=%RC%
  exit /b %RC%
)

exit /b 0
