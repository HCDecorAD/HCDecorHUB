import json,pathlib,datetime
root=pathlib.Path(__file__).resolve().parents[1]
def load(rel):
 try:return json.loads((root/rel).read_text(encoding="utf-8-sig"))
 except Exception:return None
run=load("runtime/acceptance-run.json") or {};rid=run.get("run_id","")
cp1=load("logs/cp1-real-cdp.json") or {};rows=cp1.get("pages") or cp1.get("targets") or [];ids={str(x.get("conversation_id") or "") for x in rows if x.get("conversation_id")}
reg=load("data/chats.json") or {};aliases=reg.get("aliases") or reg.get("chats") or (reg if isinstance(reg,dict) else {});bound=[v for v in aliases.values() if isinstance(v,dict) and v.get("conversation_id")]
ui=load("logs/ui-runtime-probe.json");live=load("logs/a5-live-verifies.json") or {};results=[x for x in live.get("results",[]) if x.get("run_id")==rid and x.get("ok") is True]
live_ids={x.get("conversation_id") for x in results if x.get("conversation_id")};rst=load("logs/cp1-alias-restart.json");stop=load("logs/stop-all-evidence.json")
pkg=root/"dist"/"HC-Agent-Control-V1-EarlyUse"
same=lambda x:bool(x and x.get("run_id")==rid)
checks={"A1_detect":same(cp1) and len(ids)>=2,"A2_alias":len({x["conversation_id"] for x in bound})>=2,"A3_ui":same(ui) and ui.get("pass") is True,"A4_A5_two_exact_live":same(live) and len(live_ids)>=2,"A6_restart":same(rst) and rst.get("pass") is True,"STOP_ALL":same(stop) and stop.get("pass") is True,"A7_package":pkg.is_dir() and any(pkg.iterdir())}
row={"schema":"hc-autochat-public-status/v3","run_id":rid,"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"checks":checks,"evidence":{"conversation_ids":sorted(ids),"bound_alias_count":len(bound),"live_conversation_ids":sorted(live_ids)},"public_ready":bool(rid) and all(checks.values())}
(root/"logs").mkdir(exist_ok=True);(root/"logs"/"autochat-public-status.json").write_text(json.dumps(row,indent=2),encoding="utf-8");print(json.dumps(row,indent=2));raise SystemExit(0 if row["public_ready"] else 94)
