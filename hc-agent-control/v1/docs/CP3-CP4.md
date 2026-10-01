# CP3 / CP4 status

CP3 dashboard staging:
- Registry-backed chat table.
- CDP-backed ONLINE/OFFLINE status.
- Queue-backed Dry Run.
- STOP ALL persists through queue state.
- Quick commands.
- Live log.
- Theme model supports Dark/Light/System; visual theme wiring remains open.

CP4 automated recovery:
- Online probe.
- Offline detection.
- Optional ensure callback.
- Retry after ensure.
- Live managed-Edge restart evidence remains required before CP4 production PASS.

Safety:
- UI has no real Send action.
- No fixed screen coordinates.
- No title-only authorization.
- Real send remains gated behind live identity and G2 evidence.
