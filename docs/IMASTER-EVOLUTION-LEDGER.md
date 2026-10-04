# iMaster Evolution Ledger
Version: 2026-10-04
Status: PUBLIC OPERATING HISTORY

## Lineage
1. **DONE protocol** — finish work with explicit evidence instead of conversational completion. Evidence: commit `da26befff42788da9c60ffc8b46a7358113ebf89` added HC Group DONE protocol enforcement.
2. **HC Group DONE launcher** — operationalized DONE as an executable group launcher. Evidence: `8517c87ab5c67c333cea709f7edd5887f3e26e0d` added `hc-group-done.bat`.
3. **HC DONE freeze / repeatable gates** — D06 freeze recorded verified CI evidence. Evidence: `16d17ce38aad2ff1f3f7e9df1935bece09217d3d`.
4. **Durable mission ownership** — work stopped depending on a chat turn; durable mission queue introduced. Evidence: `efacb51f04377224d707995558740c1c13edb775` added `lib/governor/durable-queue.mjs`.
5. **Master Agent orchestration** — agent bridge and Master Agent E2E became part of the execution/quality path. Evidence: `1589d1f79c76f8c9737cb6c34657b4ce26933079`, `5f2dd56040893691e08d715cbdaea89723e7e230`.
6. **iMaster ALL Phase** — verified phase state and external provisioning handoff became machine-readable and CI-gated. Evidence: `a15944f40d242f780936e1a21a281677adf6eced`.
7. **Capability Acquisition** — TOOL_MISSING ceased to be terminal; discover/acquire/verify/execute became policy. Evidence: `adec57ce573a297ed8f7a956eff3324c1ec9be45`.
8. **Bootstrap V2 / Anti-Amnesia** — one startup authority restores corporate state, capabilities, checkpoints and laws across new chats/sessions. Current PR phase.

## Evolution invariant
DONE -> HC DONE -> Durable Mission -> Master Agent -> iMaster.
Each stage inherits prior evidence discipline; later names do not erase earlier contracts.

## Next architecture baseline
The next iMaster generation adds machine contracts for: Global State Graph, Continuous Planner, Worker Factory, Self-Healing Learning Loop, Universal Operator Gateway, and Public State Service. A contract is not runtime-LIVE until its implementation and acceptance evidence pass.
