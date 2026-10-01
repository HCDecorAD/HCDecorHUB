import json,pathlib,sys,datetime,hashlib
root=pathlib.Path(__file__).resolve().parents[1]
cid=sys.argv[1] if len(sys.argv)>1 else ""
text=" ".join(sys.argv[2:]).strip()
if not cid or not text:print("ARM_DENIED conversation_id and exact command required");raise SystemExit(367)
now=datetime.datetime.now(datetime.timezone.utc);exp=now+datetime.timedelta(minutes=2)
row={"scope":"G2_SINGLE_COMMAND","conversation_id":cid,"command_sha256":hashlib.sha256(text.encode("utf-8")).hexdigest(),"created":now.isoformat(),"expires":exp.isoformat(),"uses":1}
p=root/"runtime"/"G2_LIVE_ARM.json";p.parent.mkdir(exist_ok=True);p.write_text(json.dumps(row,indent=2),encoding="utf-8")
print("AUTOCHAT_SINGLE_SEND_ARMED",cid,row["command_sha256"])
