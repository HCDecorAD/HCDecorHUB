import json,pathlib,sys,datetime
root=pathlib.Path(__file__).resolve().parents[1]
reg=json.loads((root/"data"/"chats.json").read_text(encoding="utf-8"))
aliases=reg.get("aliases") or reg.get("chats") or {}
pages_doc=json.loads((root/"logs"/"cp1-real-cdp.json").read_text(encoding="utf-8"))
pages=pages_doc.get("pages") or pages_doc.get("targets") or (pages_doc if isinstance(pages_doc,list) else [])
by={str(p.get("conversation_id") or ""):p for p in pages if p.get("conversation_id")}
rows=[];fail=[]
for alias,s in aliases.items():
 if not isinstance(s,dict):continue
 cid=str(s.get("conversation_id") or "");cur=by.get(cid)
 if not cur:fail.append(alias);continue
 rows.append({"alias":alias,"conversation_id":cid,"saved_target_id":s.get("target_id",""),"current_target_id":cur.get("target_id",""),"target_changed":bool(s.get("target_id") and cur.get("target_id") and s.get("target_id")!=cur.get("target_id"))})
passed=bool(rows) and not fail
out={"schema":"hc-autochat-alias-restart/v2","ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"pass":passed,"rows":rows,"failed_aliases":fail}
(root/"logs"/"cp1-alias-restart.json").write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding="utf-8")
if not passed:print("ALIAS_RESTART_FAIL",",".join(fail) if fail else "no aliases");raise SystemExit(97)
print("ALIAS_RESTART_EVIDENCE_PASS",len(rows))
