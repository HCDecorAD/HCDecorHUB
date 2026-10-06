@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HC Dual-Lane Mobile - P1 to P10
set ROOT=%~dp0
set LOG=%ROOT%logs
if not exist "%LOG%" mkdir "%LOG%"

echo [HC] P1-P10 orchestrator
for /L %%P in (1,1,10) do (
  echo ===== P%%P =====
  if exist "%ROOT%phases\P%%P.cmd" (
    call "%ROOT%phases\P%%P.cmd" > "%LOG%\P%%P.log" 2>&1
    if errorlevel 1 (
      echo [BLUE] P%%P FAIL - stop dependent chain
      exit /b 1
    )
    echo [GREEN] P%%P PASS
  ) else (
    echo [BLUE] P%%P runner pending
  )
)
echo [GREEN] P1-P10 COMPLETE
exit /b 0
