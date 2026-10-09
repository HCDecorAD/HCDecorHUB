@echo off
setlocal EnableExtensions EnableDelayedExpansion
set "ROOT=%~1"
if not defined ROOT set "ROOT=D:\HCDecorHUB\HC_Visual_Builder"
if not exist "%ROOT%\package.json" (
 echo ERROR: Specify the HC_Visual_Builder path as first argument.
 exit /b 2
)
cd /d "%ROOT%"
if not exist "evidence" mkdir "evidence"
del /q evidence\gate-*.exit 2>nul
echo Launching four independent workers...
start "HC Build" /min cmd /c "npm run build > evidence\gate-build.log 2>&1 & echo %%errorlevel%% > evidence\gate-build.exit"
start "HC Lint" /min cmd /c "npm run lint > evidence\gate-lint.log 2>&1 & echo %%errorlevel%% > evidence\gate-lint.exit"
start "HC WB04" /min cmd /c "node wb04-e2e.mjs > evidence\gate-wb04.log 2>&1 & echo %%errorlevel%% > evidence\gate-wb04.exit"
start "HC WB05" /min cmd /c "node wb05-e2e.mjs > evidence\gate-wb05.log 2>&1 & echo %%errorlevel%% > evidence\gate-wb05.exit"
set /a tries=0
:wait
set /a count=0
for %%G in (build lint wb04 wb05) do if exist "evidence\gate-%%G.exit" set /a count+=1
if !count! GEQ 4 goto report
set /a tries+=1
if !tries! GEQ 90 (
 echo TIMEOUT: inspect evidence logs. PUBLIC NOT CERTIFIED.
 exit /b 124
)
timeout /t 2 /nobreak >nul
goto wait
:report
set "failed=0"
for %%G in (build lint wb04 wb05) do (
 set "result=" 
 set /p result=<"evidence\gate-%%G.exit"
 echo %%G exit=!result!
 if not "!result!"=="0" set "failed=1"
)
echo Local gates finished. Live publish verification is a separate mandatory gate.
exit /b !failed!
