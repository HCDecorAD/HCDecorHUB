import pathlib,subprocess,sys,json,datetime
root=pathlib.Path(__file__).resolve().parents[1]
steps=[("A1","11_CAPTURE_CP1_EVIDENCE.bat"),("A1_VALIDATE","12_VALIDATE_CP1_LIVE.bat"),("A3","32_UI_RUNTIME_PROBE.bat"),("A3_COMPILE","47_STATIC_COMPILE_ALL.bat")]
results={}
for name,bat in steps:
 r=subprocess.run(["cmd","/c",str(root/"scripts"/bat)],cwd=root);results[name]=r.returncode
 if r.returncode:break
row={"schema":"hc-autochat-fasttrack/v1","ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"results":results,"real_send_enabled":False}
(root/"logs").mkdir(exist_ok=True);(root/"logs"/"autochat-fasttrack.json").write_text(json.dumps(row,indent=2),encoding="utf-8")
print(json.dumps(row,indent=2));raise SystemExit(0 if all(v==0 for v in results.values()) else 340)
