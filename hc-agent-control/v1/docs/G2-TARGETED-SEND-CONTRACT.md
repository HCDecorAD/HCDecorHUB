# G2 Targeted Send Contract

This contract is staged only. Real send remains disabled.

Before submit:
- resolve alias by exact conversation_id;
- re-inspect active target immediately before submit;
- exact conversation_id must match;
- title and screen coordinates cannot authorize;
- capture pre-submit user-message count.

Submit:
- adapter targets the verified CDP page;
- one command, one idempotency key;
- no broadcast fallback.

After submit:
- re-read conversation_id;
- require user-message count increase;
- require last user text exactly equals requested command;
- mismatch raises verification error and records audit.

G2 cannot PASS from unit tests. It requires controlled live evidence on HOCUONG.
