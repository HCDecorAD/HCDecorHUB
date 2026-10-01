@echo off
setlocal EnableDelayedExpansion
cd /d %~dp0..
set /a N=0
:wait
set /a N+=1
set READY=1
for %%N in (unit stress recovery ui) do if not exist "logs\parallel-%%N.exit" set READY=0
if "!READY!"=="1" goto done
if !N! GEQ 60 (echo PARALLEL_TIMEOUT&exit /b 72)
timeout /t 1 /nobreak >nul
goto wait
:done
set FAIL=0
for %%N in (unit stress recovery ui) do (
 set /p CODE=<"logs\parallel-%%N.exit"
 echo %%N exit=!CODE!
 if not "!CODE!"=="0" set /a FAIL+=1
)
if !FAIL! NEQ 0 (echo PARALLEL_CHECKS_FAIL count=!FAIL!&exit /b 73)
echo PARALLEL_CHECKS_PASS
