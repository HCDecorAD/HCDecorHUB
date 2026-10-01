import datetime,json,pathlib,sys,hashlib
root=pathlib.Path(__file__).resolve().parents[1]
name=sys.argv[1] if len(sys.argv)>1 else "UNKNOWN";evidence=sys.argv[2] if len(sys.argv)>2 else ""
ep=(root/evidence) if evidence and not pathlib.Path(evidence).is_absolute() else pathlib.Path(evidence) if evidence else None
digest=hashlib.sha256(ep.read_bytes()).hexdigest() if ep and ep.exists() and ep.is_file() else None
out=root/"checkpoints"/(name+".json");out.parent.mkdir(parents=True,exist_ok=True)
row={"schema":"hc-agent-control-checkpoint/v1","name":name,"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"evidence":evidence,"evidence_sha256":digest,"production_gate_changed":False}
out.write_text(json.dumps(row,ensure_ascii=False,indent=2),encoding="utf-8");print("CHECKPOINT_WRITTEN",out)
