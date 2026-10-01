import json,pathlib,datetime
root=pathlib.Path(__file__).resolve().parents[1]
def load(rel,default):
 p=root/rel
 try:return json.loads(p.read_text(encoding="utf-8"))
 except Exception:return default
g=load("release-gates.json",{"gates":{}});r=load("data/chats.json",{"chats":{}});q=load("data/queue.json",{"items":[]})
report={"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"gates":g.get("gates",{}),"aliases":sorted(r.get("chats",{})),"queue":{"total":len(q.get("items",[])),"global_paused":q.get("global_paused",False),"paused_aliases":q.get("paused_aliases",[])}}
out=root/"logs"/"status-report.json";out.parent.mkdir(parents=True,exist_ok=True);out.write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding="utf-8")
print("STATUS_REPORT_PASS");print(json.dumps(report,ensure_ascii=False,indent=2))
