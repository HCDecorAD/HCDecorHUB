# Runtime Reality Gate

FINAL E2E in-app acceptance proves the Android modules execute on a real device. It does **not** prove remote commands from ChatGPT can reach the phone.

## Production runtime gate
1. Agent exposes an authenticated callable transport.
2. Pairing secret is persisted in Android secure storage.
3. External controller submits HC Mobile Command JSON.
4. Agent executes a harmless command (device.info / media.list).
5. Mutation test uses preview -> confirm -> trash -> verify -> restore.
6. Controller receives signed/verified result.
7. Disconnect/reconnect resumes queued job by jobId without duplicate execution.

Until steps 1-7 are verified, status is RUNTIME_TRANSPORT_BLOCKED, not production remote-control PASS.

Current blocker: this ChatGPT session has no direct Android network/socket executor and the app does not yet expose a reachable authenticated server/client transport.
