import json,pathlib,hashlib,datetime,subprocess
root=pathlib.Path(__file__).resolve().parents[1]
files=["src/ui/app.py","src/core/settings.py","src/core/live_status.py","src/core/queue.py","release-gates.json"]
rows=[]
for rel in files:
 p=root/rel
 rows.append({"path":rel,"exists":p.exists(),"sha256":hashlib.sha256(p.read_bytes()).hexdigest() if p.exists() else None})
out={"schema":"hc-agent-control-acceptance/v1","ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"files":rows,"real_send_expected":False}
p=root/"logs"/"acceptance-manifest.json";p.parent.mkdir(parents=True,exist_ok=True);p.write_text(json.dumps(out,indent=2),encoding="utf-8");print("ACCEPTANCE_MANIFEST_PASS",p)
