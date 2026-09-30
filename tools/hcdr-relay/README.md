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

The agent posts a `hcdr-result/v1` JSON comment after execution.

Do not place tokens, secrets, .env contents, credentials, or production keys in jobs/results.
