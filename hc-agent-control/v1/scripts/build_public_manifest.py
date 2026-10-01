import json,pathlib,datetime,sys
root=pathlib.Path(__file__).resolve().parents[1]
run=json.loads((root/"runtime"/"acceptance-run.json").read_text(encoding="utf-8"));rid=run["run_id"]
required=["cp1-real-cdp.json","ui-runtime-probe.json","a5-live-verifies.json","cp1-alias-restart.json","stop-all-evidence.json","autochat-public-status.json"]
ev={}
for name in required:
 p=root/"logs"/name
 if not p.exists():raise SystemExit("MISSING "+name)
 d=json.loads(p.read_text(encoding="utf-8"))
 if d.get("run_id")!=rid:raise SystemExit("STALE_OR_FOREIGN "+name)
 ev[name]=True
row={"schema":"hc-autochat-acceptance-manifest/v1","run_id":rid,"created":datetime.datetime.now(datetime.timezone.utc).isoformat(),"evidence":ev,"pass":True}
out=root/"logs"/"PUBLIC-ACCEPTANCE.json";out.write_text(json.dumps(row,indent=2),encoding="utf-8")
print("ACCEPTANCE_MANIFEST_PASS",rid)
