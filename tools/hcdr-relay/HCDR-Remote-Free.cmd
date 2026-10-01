@echo off
setlocal
set HCDR_RELAY_REPO=HCDecorAD/HCDecor-HCDR-Relay
set HCDR_ROOT=D:/HCDecorHUB
set HCDR_RELAY_SELF_UPDATE=0
cd /d D:\HCDecorHUB\repos\HCDecorHUB || exit /b 70
if not exist tools\hcdr-relay\relay-agent.mjs (echo HCDR_AGENT_MISSING&exit /b 71)
node --check tools\hcdr-relay\relay-agent.mjs || exit /b 72
node tools\hcdr-relay\relay-agent.mjs
