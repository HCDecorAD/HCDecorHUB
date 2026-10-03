@echo off
set HCDR_RELAY_REPO=HCDecorAD/HCDecor-HCDR-Relay
set HCDR_ROOT=D:/HCDecorHUB
set HCDR_MAX_WORKERS=4
set HCDR_JOB_TIMEOUT_MS=900000
set HCDR_HEARTBEAT_MS=5000
cd /d D:\HCDecorHUB\repos\HCDecorHUB
git pull --ff-only
node tools\hcdr-relay\relay-agent.mjs
