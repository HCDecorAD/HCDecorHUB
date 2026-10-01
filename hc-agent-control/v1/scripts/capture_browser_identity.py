import json,pathlib,urllib.request,datetime
root=pathlib.Path(__file__).resolve().parents[1]
with urllib.request.urlopen("http://127.0.0.1:9222/json/version",timeout=2) as r:v=json.load(r)
with urllib.request.urlopen("http://127.0.0.1:9222/json",timeout=2) as r:pages=json.load(r)
row={"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"browser":v.get("Browser"),"websocket":v.get("webSocketDebuggerUrl"),"targets":sorted(x.get("id","") for x in pages if x.get("type")=="page")}
name=__import__("sys").argv[1] if len(__import__("sys").argv)>1 else "browser-identity"
p=root/"logs"/(name+".json");p.parent.mkdir(parents=True,exist_ok=True);p.write_text(json.dumps(row,indent=2),encoding="utf-8");print("BROWSER_IDENTITY_PASS",p)
