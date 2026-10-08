# HC MEMORY CORE — Acceptance checkpoint 2026-10-08

## Verified
- GitHub Memory Core bootstrap, registry and startup procedure readable through connected GitHub API.
- Mesh health OK, local-direct.
- Gateway UP, Zeus Z06, 1 client, Owner Lock true, as reported by mesh_status.

## Blocked
- New Chat test via Zeus create_chat returned `VERIFY_TIMEOUT`, `ok:false`, tabId `1882659376`.
- Follow-up list_chat_tabs did not show a new tab with valid CID.
- No verified retrieval of Memory Core by a fresh Chat.
- No verified HOCUONG filesystem read/write test via either primary lane.
- File-level source index still pending.

## Rules
Do not mark automatic Memory loading as PASS. Do not retry new-chat creation in a loop. Avoid RDC/HCDR except explicit rescue. Never report local source/test verification based solely on GitHub metadata.

## Next acceptance actions
1. Inspect Zeus create_chat VERIFY_TIMEOUT in local Zeus/Gateway logs using an authorized primary lane.
2. Verify a fresh Chat can retrieve both 00-BOOTSTRAP.md and 03-PROJECT-REGISTRY.json and cite exact commit evidence.
3. Confirm local HOCUONG read and write through primary lane with a reversible test in HC-Memory-Core, not in an existing PASS module.
4. Populate file-level Source Index from actual source tree and log evidence.
5. Configure ChatGPT Project instructions; GitHub file existence alone cannot enable automatic loading.
