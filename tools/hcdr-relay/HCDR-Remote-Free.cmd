@echo off
set HCDR_RELAY_REPO=HCDecorAD/HCDecor-HCDR-Relay
set HCDR_ROOT=D:/HCDecorHUB
cd /d D:\HCDecorHUB\repos\HCDecorHUB
git pull --ff-only
node tools\hcdr-relay\relay-agent.mjs
