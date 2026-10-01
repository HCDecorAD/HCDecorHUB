@echo off
setlocal
set HCDR_RELAY_REPO=HCDecorAD/HCDecor-HCDR-Relay
set HCDR_V12_LABEL=hcdr-v12-test
set HCDR_V12_SOURCE=hocuong-v12-production
set HCDR_V11_ROOT=D:\HCDecorHUB\HCDR Remote MCP
cd /d D:\HCDecorHUB\repos\HCDecorHUB
node tools\hcdr-relay\relay-agent-v12.mjs
