import json,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1];p=root/"logs"/"cp1-real-cdp.json"
if not p.exists():print("CP1_LIVE_FAIL evidence missing");raise SystemExit(91)
pages=json.loads(p.read_text(encoding="utf-8"));ids=[str(x.get("conversation_id") or "").strip() for x in pages];ids=[x for x in ids if x]
if len(set(ids))<2:print("CP1_LIVE_FAIL need >=2 distinct conversations");raise SystemExit(92)
print("CP1_LIVE_MULTI_CHAT_EVIDENCE_PASS",len(set(ids)))
