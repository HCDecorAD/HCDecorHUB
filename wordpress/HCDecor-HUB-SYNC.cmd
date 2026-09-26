@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title HCDecor HUB - Manual Sync

where wp >nul 2>&1 || (
  echo [FAIL] Open this file from LocalWP Site Shell environment.
  echo.
  pause
  exit /b 2
)

call wp core is-installed >nul 2>&1 || (
  echo [FAIL] WordPress is not ready.
  echo.
  pause
  exit /b 2
)

set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress"
set "SERVICE=HCDecor-HUB-SERVICES.cmd"
set "TMP=%SERVICE%.new"
set "V=%RANDOM%%RANDOM%%RANDOM%"

echo [HCDecor HUB] Downloading latest sync engine...
curl.exe -fL "%BASE%/%SERVICE%?v=%V%" -o "%TMP%"
if errorlevel 1 (
  echo [FAIL] Cannot download latest sync engine.
  if exist "%TMP%" del /q "%TMP%" >nul 2>&1
  echo.
  pause
  exit /b 1
)

move /y "%TMP%" "%SERVICE%" >nul
if errorlevel 1 (
  echo [FAIL] Cannot update local sync engine.
  echo.
  pause
  exit /b 1
)

echo [HCDecor HUB] Syncing latest modules...
call "%SERVICE%"
set "RC=%ERRORLEVEL%"

echo.
if "%RC%"=="0" (
  echo ==============================================
  echo HCDECOR HUB SYNC: READY
  echo ==============================================
) else (
  echo ==============================================
  echo HCDECOR HUB SYNC: FAILED ^(code %RC%^)
  echo ==============================================
)
echo.
pause
exit /b %RC%
