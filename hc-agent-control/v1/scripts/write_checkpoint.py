import datetime,json,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1]
name=sys.argv[1] if len(sys.argv)>1 else "UNKNOWN";evidence=sys.argv[2] if len(sys.argv)>2 else ""
out=root/"checkpoints"/(name+".json");out.parent.mkdir(parents=True,exist_ok=True)
row={"schema":"hc-agent-control-checkpoint/v1","name":name,"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"evidence":evidence,"production_gate_changed":False}
out.write_text(json.dumps(row,ensure_ascii=False,indent=2),encoding="utf-8");print("CHECKPOINT_WRITTEN",out)
