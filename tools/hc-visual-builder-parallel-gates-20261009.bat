@echo off
setlocal EnableExtensions
cd /d "%~dp0"
if not exist "package.json" (
 echo ERROR: Put this BAT in HC_Visual_Builder directory.
 exit /b 2
)
if not exist "evidence" mkdir "evidence"
echo Running independent local gates in parallel...
start "HC Build" /wait cmd /c "npm run build > evidence\build.log 2>&1 & echo %%errorlevel%% > evidence\build.exit"
start "HC Lint" /wait cmd /c "npm run lint > evidence\lint.log 2>&1 & echo %%errorlevel%% > evidence\lint.exit"
start "HC WB04" /wait cmd /c "node wb04-e2e.mjs > evidence\wb04.log 2>&1 & echo %%errorlevel%% > evidence\wb04.exit"
start "HC WB05" /wait cmd /c "node wb05-e2e.mjs > evidence\wb05.log 2>&1 & echo %%errorlevel%% > evidence\wb05.exit"
echo Gates launched. Check evidence folder; this script does NOT certify public deployment.
endlocal
