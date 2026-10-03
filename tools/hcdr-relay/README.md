# HCDR Remote Free relay agent

Outbound-only relay for ChatGPT Web Plus -> GitHub private relay repo -> HCDR local executor.

Safety defaults:
- no inbound port or tunnel;
- relay repository MUST be private;
- first phase is read/build only: health, list_directory, read_file, git_status, git_diff, build;
- production mutation remains blocked by the existing HCDR guard;
- GitHub authentication stays in the local gh CLI credential store.

Local start:

```powershell
$env:HCDR_RELAY_REPO="HCDecorAD/HCDecor-HCDR-Relay"
node D:\HCDecorHUB\repos\HCDecorHUB\tools\hcdr-relay\relay-agent.mjs
```

The relay repository uses GitHub issues labelled `hcdr-job`. Issue body:

```json
{"schema":"hcdr-relay/v1","tool":"health","args":{}}
```

The agent posts a `hcdr-result/v2` JSON comment after execution and echoes `source_id`, `mission_id`, and `correlation_id` when present in the job body.

Do not place tokens, secrets, .env contents, credentials, or production keys in jobs/results.


## Always-on remote receiver

For unattended Mobile -> GitHub Relay -> HOCUONG operation, install the persistent receiver once from an elevated terminal:

```bat
hcdr-install-always-on.bat
```

This installs a Scheduled Task for startup/logon plus a one-minute watchdog. The watchdog validates the relay heartbeat and Node PID, removes only stale locks, and restarts the read/build relay when needed. Production mutation remains blocked by the existing HCDR guard.

Evidence of a live laptop roundtrip still requires an actual relay result; this installation contract does not fake HOCUONG runtime GREEN.
