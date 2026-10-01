import json,pathlib,subprocess,time,urllib.request,datetime
root=pathlib.Path(__file__).resolve().parents[1];out=root/"logs"/"cp4-recovery.json";out.parent.mkdir(parents=True,exist_ok=True)
def alive():
 try:
  with urllib.request.urlopen("http://127.0.0.1:9222/json",timeout=2) as r:return r.status==200
 except Exception:return False
before=alive()
subprocess.run(["cmd","/c",str(root/"scripts"/"03_ENSURE_MANAGED_EDGE.bat")],cwd=root)
for _ in range(15):
 if alive():break
 time.sleep(1)
after=alive();row={"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"before":before,"after":after,"recovery_path":"03_ENSURE_MANAGED_EDGE.bat"}
out.write_text(json.dumps(row,indent=2),encoding="utf-8")
if not after:print("CP4_LIVE_RECOVERY_FAIL");raise SystemExit(104)
print("CP4_LIVE_RECOVERY_EVIDENCE_PASS")
