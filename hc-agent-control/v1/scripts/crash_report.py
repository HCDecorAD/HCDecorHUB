import json,pathlib,platform,sys,datetime,traceback
root=pathlib.Path(__file__).resolve().parents[1];out=root/"logs"/"crash-report.json";out.parent.mkdir(parents=True,exist_ok=True)
row={"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"python":sys.version,"platform":platform.platform(),"send_armed":False}
try:row["manifest"]=json.loads((root/"manifest.json").read_text(encoding="utf-8"));row["send_armed"]=bool(row["manifest"].get("send_armed"))
except Exception as e:row["manifest_error"]=str(e)
out.write_text(json.dumps(row,ensure_ascii=False,indent=2),encoding="utf-8");print("CRASH_CONTEXT_WRITTEN",out)
