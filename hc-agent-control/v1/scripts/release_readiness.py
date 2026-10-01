import json,pathlib,datetime,urllib.request
root=pathlib.Path(__file__).resolve().parents[1]
g=json.loads((root/"release-gates.json").read_text(encoding="utf-8"))
blocked={k:v for k,v in g["gates"].items() if k in g["required_for_v1_production"] and v!="PASS"}
cdp=False
try:
 with urllib.request.urlopen("http://127.0.0.1:9222/json",timeout=1) as r:cdp=r.status==200
except Exception:pass
row={"schema":"hc-agent-control-readiness/v1","ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"production_ready":not blocked,"blocked_gates":blocked,"cdp_online":cdp,"real_send_gate":g["gates"].get("G2_REAL_TARGETED_SEND"),"next_required":["HOCUONG live acceptance"] if blocked else []}
p=root/"logs"/"release-readiness.json";p.parent.mkdir(parents=True,exist_ok=True);p.write_text(json.dumps(row,indent=2),encoding="utf-8");print(json.dumps(row,indent=2))
raise SystemExit(0 if not blocked else 40)
