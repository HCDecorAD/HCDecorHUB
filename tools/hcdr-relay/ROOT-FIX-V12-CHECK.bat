@echo off
setlocal
set AGENT=D:\HCDecorHUB\repos\HCDecorHUB\tools\hcdr-relay\relay-agent-v12.mjs
if not exist "%AGENT%" (
 echo V12_BLOCKED_AGENT_MISSING
 echo Do not start HCDR-V12-Test.cmd until relay-agent-v12.mjs is implemented and tested.
 exit /b 20
)
node --check "%AGENT%" || exit /b 21
echo V12_AGENT_STATIC_PASS
