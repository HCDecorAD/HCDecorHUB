import json,pathlib,sys,datetime
root=pathlib.Path(__file__).resolve().parents[1]
cid=sys.argv[1] if len(sys.argv)>1 else ""
if not cid:print("ARM_DENIED conversation_id required");raise SystemExit(367)
p=root/"runtime"/"G2_LIVE_ARM.json";p.parent.mkdir(exist_ok=True)
p.write_text(json.dumps({"scope":"G2_SINGLE_COMMAND","conversation_id":cid,"created":datetime.datetime.now(datetime.timezone.utc).isoformat(),"uses":1},indent=2),encoding="utf-8")
print("AUTOCHAT_SINGLE_SEND_ARMED",cid)
