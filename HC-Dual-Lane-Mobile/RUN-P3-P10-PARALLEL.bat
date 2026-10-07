@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HC Mobile P3-P10 Parallel
set ROOT=%~dp0
set LOG=%ROOT%logs
if not exist "%LOG%" mkdir "%LOG%"

echo [BLUE] START P3-P10 PARALLEL
for %%P in (3 4 5 6 7 8 9 10) do (
  if exist "%ROOT%phases\P%%P.cmd" (
    start "HC-P%%P" /b cmd /c "call ""%ROOT%phases\P%%P.cmd"" > ""%LOG%\P%%P.log"" 2>&1 & echo !errorlevel! > ""%LOG%\P%%P.exit"""
  ) else (
    echo 2 > "%LOG%\P%%P.exit"
  )
)

:WAIT
set DONE=0
for %%P in (3 4 5 6 7 8 9 10) do if exist "%LOG%\P%%P.exit" set /a DONE+=1
if not "!DONE!"=="8" (
  timeout /t 2 /nobreak >nul
  goto WAIT
)

set FAIL=0
for %%P in (3 4 5 6 7 8 9 10) do (
  set /p CODE=<"%LOG%\P%%P.exit"
  if "!CODE!"=="0" (echo [GREEN] P%%P RUNNER OK) else (echo [BLUE] P%%P RUNNER FAIL & set FAIL=1)
)
if "!FAIL!"=="1" exit /b 1
echo [GREEN] P3-P10 PARALLEL RUNNERS COMPLETE
echo Real-device PASS remains gated by acceptance tests.
exit /b 0
