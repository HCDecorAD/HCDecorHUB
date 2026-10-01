import json,pathlib,datetime
TERMINAL={"PASS","FAILED","CANCELLED"}
def compact(queue,archive_path,keep_terminal=500):
 terminal=[x for x in queue.items if x.get("state") in TERMINAL]
 active=[x for x in queue.items if x.get("state") not in TERMINAL]
 if len(terminal)<=keep_terminal:return {"archived":0,"kept":len(queue.items)}
 old=terminal[:-keep_terminal];recent=terminal[-keep_terminal:]
 p=pathlib.Path(archive_path);p.parent.mkdir(parents=True,exist_ok=True)
 existing=[]
 if p.exists():
  try:existing=json.loads(p.read_text(encoding="utf-8"))
  except Exception:existing=[]
 existing.extend(old);p.write_text(json.dumps(existing,ensure_ascii=False,indent=2),encoding="utf-8")
 queue.items=active+recent;queue.keys={x.get("idempotency_key") for x in queue.items if x.get("idempotency_key")};queue._save()
 return {"archived":len(old),"kept":len(queue.items)}
