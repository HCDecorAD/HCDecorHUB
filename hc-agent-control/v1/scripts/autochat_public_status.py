import json,pathlib,datetime
root=pathlib.Path(__file__).resolve().parents[1]
def load(rel):
 try:return json.loads((root/rel).read_text(encoding="utf-8"))
 except Exception:return None
cp1=load("logs/cp1-real-cdp.json")
rows=(cp1 or {}).get("pages") or (cp1 or {}).get("targets") or []
ids={str(x.get("conversation_id") or "") for x in rows if x.get("conversation_id")}
reg=load("data/chats.json")
aliases=(reg or {}).get("aliases",reg if isinstance(reg,dict) else {})
bound=[v for v in aliases.values() if isinstance(v,dict) and v.get("conversation_id")]
a5=load("logs/a5-live-verify.json")
rst=load("logs/cp1-alias-restart.json")
pkg=root/"dist"/"HC-Agent-Control-V1-EarlyUse"
checks={
 "A1_detect":len(ids)>=2,
 "A2_alias":len({x["conversation_id"] for x in bound})>=2,
 "A3_ui":(root/"src"/"ui"/"app.py").exists() and (root/"logs"/"ui-runtime-probe.json").exists(),
 "A4_exact_send":bool(a5 and a5.get("ok") is True and a5.get("conversation_id")),
 "A5_verify":bool(a5 and a5.get("ok") is True and a5.get("last_user_text")),
 "A6_restart":bool(rst and rst.get("pass") is True),
 "A7_package":pkg.is_dir() and any(pkg.iterdir()),
}
row={"schema":"hc-autochat-public-status/v2","ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"checks":checks,"evidence":{"conversation_ids":sorted(ids),"bound_alias_count":len(bound)},"public_ready":all(checks.values())}
(root/"logs").mkdir(exist_ok=True);(root/"logs"/"autochat-public-status.json").write_text(json.dumps(row,indent=2),encoding="utf-8")
print(json.dumps(row,indent=2));raise SystemExit(0 if row["public_ready"] else 350)
