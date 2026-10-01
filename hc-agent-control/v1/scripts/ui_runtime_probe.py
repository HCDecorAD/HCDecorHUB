import ast,pathlib,sys,json,datetime
root=pathlib.Path(__file__).resolve().parents[1];files=list((root/"src"/"ui").glob("*.py"))
for p in files:ast.parse(p.read_text(encoding="utf-8"),filename=str(p))
sys.path.insert(0,str(root));import src.ui.app,src.ui.first_run
rid=""
try:rid=json.loads((root/"runtime"/"acceptance-run.json").read_text(encoding="utf-8")).get("run_id","")
except Exception:pass
row={"schema":"hc-autochat-ui-probe/v2","run_id":rid,"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"pass":True,"files":len(files)}
(root/"logs").mkdir(exist_ok=True);(root/"logs"/"ui-runtime-probe.json").write_text(json.dumps(row,indent=2),encoding="utf-8")
print("UI_RUNTIME_PROBE_PASS",len(files))
