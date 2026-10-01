# HC AutoChat V1 — HOCUONG Live Runbook

Order is strict: A1 Detect -> A2 Alias -> A3 UI -> A4 Exact Send -> A5 Verify -> A6 Restart -> A7 Package -> PUBLIC.

Safety:
- Never broadcast.
- Exact conversation_id is mandatory.
- STOP ALL / pause blocks dispatch.
- Live send is one-shot armed for one conversation only.
- A4/A5 evidence must come from a real HOCUONG run.
- PUBLIC gate validates evidence content, not file existence.

Entry BATs:
- AUTOCHAT_HOCUONG_PREFLIGHT.bat
- AUTOCHAT_HOCUONG_RUNBOOK.bat
- AUTOCHAT_LIVE_ACCEPTANCE.bat
- AUTOCHAT_PUBLIC_GATE.bat
