# HC Agent Control — AutoChat V1 Release Checklist

Production must remain blocked until every required gate is evidenced.

- [x] CP0 bootstrap/preflight previously passed locally.
- [ ] CP1 detect at least two real ChatGPT /c/ conversations through managed Edge CDP.
- [ ] CP1 bind unique aliases by conversation_id.
- [ ] CP1 restart browser/app and re-resolve aliases despite changed target_id.
- [x] CP2 dry-run safety logic staged and tested.
- [ ] G2 send one controlled command to selected real conversation only.
- [ ] G2 verify pre-submit conversation_id and post-submit landed prompt.
- [ ] G2 mismatch test proves ABORT_SEND and zero cross-chat send.
- [ ] CP3 UI live data + STOP ALL + pause/retry + theme.
- [ ] CP4 recovery from browser/CDP restart.
- [ ] CP5 packaged executable/portable smoke.
- [ ] CP6 production checkpoint and rollback artifact.

Hard rule: release script must fail while any checkbox above that maps to a required gate is open.
