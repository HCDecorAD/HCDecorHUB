# HC System Architecture

- ChatGPT/iMaster: orchestration, not a persistent file store.
- GitHub: versioned repository metadata and safe documentation.
- HOCUONG: authoritative local builds, source files, execution and test logs when connected.
- Dual Lane A: Gateway → TransWarp/Local Executor → HOCUONG.
- Dual Lane B: MeshCentral → Mesh Agent → HOCUONG.
- RDC/HCDR: rescue only, never count as primary lane acceptance.
- Memory Core: bootstrap + rules + project registry + source index + connection routes + evidence.

## Current limits
GitHub repository and latest commit metadata verified on 2026-10-08. Source file tree and local evidence not yet inspected. Mesh status probe returned internal error. New chats must explicitly retrieve the repository memory files; this bootstrap cannot force automatic tool execution.
