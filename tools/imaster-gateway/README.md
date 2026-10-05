# iMaster / HC Local Gateway

Zero-cost local-first gateway. Default bind: `127.0.0.1:8770`; existing Zeus: `127.0.0.1:8766`.

Mutation requires both `HC_GATEWAY_TOKEN` and the existing `ZEUS_OWNER_TOKEN`.

Endpoints: `GET /health`, `GET /status`, `GET /tabs`, `POST /zeus/send`, `POST /zeus/create`, `POST /zeus/rename`.

No raw public shell. HCDR is fallback; remote desktop is rescue-only.
