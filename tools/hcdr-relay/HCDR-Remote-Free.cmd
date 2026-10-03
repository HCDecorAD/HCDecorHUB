@echo off
setlocal
set HCDR_RELAY_REPO=HCDecorAD/HCDecor-HCDR-Relay
set HCDR_ROOT=D:/HCDecorHUB
set HCDR_RELAY_SELF_UPDATE=0
set HCDR_MAX_WORKERS=4
set HCDR_JOB_TIMEOUT_MS=900000
set HCDR_HEARTBEAT_MS=5000
cd /d D:\HCDecorHUB\repos\HCDecorHUB || exit /b 70
if not exist tools\hcdr-relay\relay-agent.mjs (echo HCDR_AGENT_MISSING&exit /b 71)
node --check tools\hcdr-relay\relay-agent.mjs || exit /b 72
node tools\hcdr-relay\relay-agent.mjs
