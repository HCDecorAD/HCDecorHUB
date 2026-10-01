@echo off
setlocal EnableExtensions
set REPO=D:\HCDecorHUB\repos\HCDecorHUB
if not exist "%REPO%\.git" (echo BOOTSTRAP_REPO_MISSING&exit /b 80)
cd /d "%REPO%" || exit /b 81
if not exist tools\hcdr-relay\relay-agent-v12.mjs (echo SOURCE_NOT_SYNCED_V12_AGENT_MISSING&exit /b 82)
if not exist tools\hcdr-relay\hcdr-supervisor.ps1 (echo SOURCE_NOT_SYNCED_SUPERVISOR_MISSING&exit /b 83)
call tools\hcdr-relay\ROOT-FIX-ALL.bat || exit /b 88
echo HCDR_ONE_TIME_BOOTSTRAP_PASS
exit /b 0
