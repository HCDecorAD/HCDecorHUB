import json,pathlib,shutil,datetime
root=pathlib.Path(__file__).resolve().parents[1];p=root/"data"/"queue.json";p.parent.mkdir(parents=True,exist_ok=True)
if p.exists():
 stamp=datetime.datetime.now().strftime("%Y%m%d-%H%M%S");shutil.copy2(p,root/"backups"/f"queue-{stamp}.json") if (root/"backups").exists() else None
try:d=json.loads(p.read_text(encoding="utf-8")) if p.exists() else {}
except Exception:d={}
items=d.get("items",[]) if isinstance(d.get("items",[]),list) else []
seen=set();clean=[]
for x in items:
 k=x.get("idempotency_key")
 if k and k in seen:continue
 if k:seen.add(k)
 if x.get("state") not in {"READY","RUNNING","RETRY","PASS","FAILED","CANCELLED"}:x["state"]="FAILED"
 clean.append(x)
out={"items":clean,"global_paused":bool(d.get("global_paused",True)),"paused_aliases":sorted(set(d.get("paused_aliases",[])))}
p.write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding="utf-8");print("QUEUE_REPAIR_PASS",len(clean))
