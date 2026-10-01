import json,pathlib,uuid,datetime,sys
root=pathlib.Path(__file__).resolve().parents[1]
run_id=str(uuid.uuid4())
p=root/"runtime"/"acceptance-run.json";p.parent.mkdir(exist_ok=True)
row={"schema":"hc-autochat-acceptance-run/v1","run_id":run_id,"started":datetime.datetime.now(datetime.timezone.utc).isoformat(),"state":"RUNNING"}
p.write_text(json.dumps(row,indent=2),encoding="utf-8")
print(run_id)
