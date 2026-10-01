import json,pathlib,datetime,urllib.request
root=pathlib.Path(__file__).resolve().parents[1]
def load(p,default):
 try:return json.loads(p.read_text(encoding="utf-8"))
 except Exception:return default
cdp=False;pages=0
try:
 with urllib.request.urlopen("http://127.0.0.1:9222/json",timeout=2) as r:d=json.load(r)
 cdp=True;pages=sum(1 for x in d if x.get("type")=="page" and ("chatgpt.com" in x.get("url","") or "chat.openai.com" in x.get("url","")))
except Exception:pass
ch=load(root/"data"/"chats.json",{"chats":{}});q=load(root/"data"/"queue.json",{"items":[],"global_paused":True,"paused_aliases":[]})
row={"schema":"hc-agent-control-health/v1","ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"cdp":cdp,"chat_pages":pages,"aliases":len(ch.get("chats",{})),"queue_items":len(q.get("items",[])),"stop_all":bool(q.get("global_paused",True)),"paused_aliases":q.get("paused_aliases",[])}
out=root/"logs"/"health-summary.json";out.parent.mkdir(parents=True,exist_ok=True);out.write_text(json.dumps(row,indent=2),encoding="utf-8");print(json.dumps(row))
raise SystemExit(0 if cdp else 221)
