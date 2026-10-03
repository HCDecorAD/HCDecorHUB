# HCDR Remote Free V2 — Project Handoff

Status: ACTIVE / acceptance verified on HOCUONG.
Control plane: private GitHub repository `HCDecorAD/HCDecor-HCDR-Relay`.
Local root: `D:/HCDecorHUB`.
Transport: ChatGPT GitHub connector -> Relay issue -> HCDR local executor -> result comment.
A dedicated ChatGPT tool/action named `HCDR` is NOT required. If the GitHub connector is available, HCDR is reachable through the relay repository.
Do not use Remote Desktop Commander.

## Job contract
Create an issue in the relay repository, add label `hcdr-job`, and use JSON body:
```json
{"schema":"hcdr-relay/v1.2","source":"hocuong-v12-production","tool":"health","args":{},"source_id":"<chat-purpose>","mission_id":"<mission>","correlation_id":"<correlation>"}
```
The local agent returns a fenced JSON comment with schema `hcdr-result/v2` and closes completed jobs.

## Safety
Production mutation is default LOCKED. Secrets stay local. Do not request .env, credentials, tokens, PEM/key files through relay. Run a health job before substantial work in a new chat.

## Verified acceptance
Health/local executor PASS; launcher files PASS; clean git status PASS; architecture PASS 16/16; heartbeat PASS. Windows Startup verification should be treated as pending until a clean test confirms the installed Startup entry.

## Multi-chat convention
Set `job_owner` to a short stable purpose such as `hcdecor-main`, `gsc-page`, or `amo-page`. Each chat should only interpret jobs it created or explicitly adopted. Issue number remains the authoritative job ID.

## Chat availability rule
Do not conclude `HCDR unavailable` merely because no direct HCDR action appears in the chat tool list. Check whether the GitHub connector can access `HCDecorAD/HCDecor-HCDR-Relay`; if yes, create a fresh `hcdr-job` health issue and use its `hcdr-result/v2` comment as the availability proof.
