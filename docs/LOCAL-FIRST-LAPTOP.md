# HC Local-First Laptop

Current compute provider: **HOCUONG_LAPTOP**.

## Rule

- Build/test/package work is local-first.
- GitHub is source/checkpoint/sync, not the build blocker.
- GitHub queue/rate-limit/network failure must not invalidate a completed local build.
- Runtime DATA stays outside the repository under `D:\HC_DATA`.
- Compute provider is replaceable later without changing the DATA contract.

## One-time setup

```bat
HC-LOCAL-FIRST.bat setup
```

Creates:

```text
D:\HC_DATA
  projects
  builds
  cache
  artifacts
  evidence
  queue\pending
  queue\running
  queue\done
  queue\failed
  sync\github-pending
  sync\github-synced
  sync\retry
```

## Commands

```bat
HC-LOCAL-FIRST.bat status
HC-LOCAL-FIRST.bat build
HC-LOCAL-FIRST.bat sync
```

A successful local build is **PASS DONE** even when GitHub sync is pending.

Future migration only changes the compute provider (PC/server/cloud); the DATA and queue contract remains stable.

## Hard execution route

`CODE -> HOCUONG LOCAL -> HCDR -> LOCAL TEST/EVIDENCE -> GitHub -> CI`

RDC is rescue-only. It is used only when HCDR is unavailable, failed, lacks a required capability, or a GUI-only operation is required. GitHub/CI cannot replace local test evidence for code/build changes.
