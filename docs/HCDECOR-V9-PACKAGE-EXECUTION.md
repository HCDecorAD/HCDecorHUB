# HCDecor HUB V9 — Package Execution Contract

This file freezes the execution decomposition for the V9 app.

## Operating law

CODE → HOCUONG LOCAL → HCDR → LOCAL TEST/EVIDENCE → GitHub → CI.

RDC is rescue-only. NO_EVIDENCE_NO_GREEN. DO_NOT_REDO_PASS.

## Package waves

- W0: PKG-00 APP CONTRACT
- W1: PKG-01 APP SHELL
- W2 parallel: PKG-02 iMaster Core, PKG-03 Project Center, PKG-04 System Center, PKG-05 AI Connections
- W3 parallel: PKG-06 Create Post, PKG-09 Trend Engine, PKG-10 Business Center, PKG-12 PANDA/Workers, PKG-13 Sentinel/AutoDebug, PKG-14 Local-First
- W4 parallel: PKG-07 Publish Engine, PKG-08 Multi Publish, PKG-11 UI Designer
- W5: PKG-15 Integration
- W6: PKG-16 Release Freeze

## State contract

READY → BUILDING → LOCAL_PASS → INTEGRATED → DONE.
Use WAITING_DEP only for a real unresolved dependency. A waiting package must not block unrelated packages.

## UI source of truth

Window and module IDs are immutable contracts. Labels may switch EN/VN, IDs do not change.
Each package must reuse the shared APP SHELL instead of creating its own top bar/sidebar.

## DONE rule

A package is DONE only when every declared done_gate has evidence.
No button may be accepted if it has no mapped real action.
