import json,pathlib,subprocess,time,urllib.request,datetime,sys
root=pathlib.Path(__file__).resolve().parents[1];out=root/"logs"/"cp4-recovery.json";out.parent.mkdir(parents=True,exist_ok=True)
def snapshot():
 try:
  with urllib.request.urlopen("http://127.0.0.1:9222/json/version",timeout=2) as r:v=json.load(r)
  with urllib.request.urlopen("http://127.0.0.1:9222/json",timeout=2) as r:p=json.load(r)
  return {"alive":True,"websocket":v.get("webSocketDebuggerUrl"),"targets":sorted(x.get("id","") for x in p if x.get("type")=="page")}
 except Exception:return {"alive":False,"websocket":None,"targets":[]}
require_down="--require-down" in sys.argv
before=snapshot()
if require_down and before["alive"]:
 row={"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"before":before,"after":before,"required_down":True,"pass":False,"reason":"endpoint still alive before recovery"}
 out.write_text(json.dumps(row,indent=2),encoding="utf-8");print("CP4_STRONG_FAIL endpoint must be down first");raise SystemExit(104)
subprocess.run(["cmd","/c",str(root/"scripts"/"03_ENSURE_MANAGED_EDGE.bat")],cwd=root)
after={"alive":False}
for _ in range(20):
 after=snapshot()
 if after["alive"]:break
 time.sleep(1)
row={"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"before":before,"after":after,"required_down":require_down,"pass":bool(after["alive"] and (not require_down or not before["alive"]))}
out.write_text(json.dumps(row,indent=2),encoding="utf-8")
if not row["pass"]:print("CP4_LIVE_RECOVERY_FAIL");raise SystemExit(105)
print("CP4_STRONG_RECOVERY_PASS" if require_down else "CP4_RECOVERY_PASS")
