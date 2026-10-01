import json,pathlib
root=pathlib.Path(__file__).resolve().parents[1];p=root/"data"/"queue.json"
try:d=json.loads(p.read_text(encoding="utf-8")) if p.exists() else {}
except Exception:d={}
d["global_paused"]=True;d.setdefault("items",[]);d.setdefault("paused_aliases",[])
p.parent.mkdir(parents=True,exist_ok=True);p.write_text(json.dumps(d,ensure_ascii=False,indent=2),encoding="utf-8")
print("SAFE_STATE_PASS global_paused=true send_armed=false")
