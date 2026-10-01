import json,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1]
reg=json.loads((root/"data"/"chats.json").read_text(encoding="utf-8")).get("chats",{})
pages=json.loads((root/"logs"/"cp1-real-cdp.json").read_text(encoding="utf-8"))
by={str(p.get("conversation_id") or ""):p for p in pages if p.get("conversation_id")}
rows=[];fail=[]
for alias,s in reg.items():
 cid=str(s.get("conversation_id") or "");cur=by.get(cid)
 if not cur:fail.append(alias);continue
 rows.append({"alias":alias,"conversation_id":cid,"saved_target_id":s.get("target_id",""),"current_target_id":cur.get("target_id",""),"target_changed":bool(s.get("target_id") and cur.get("target_id") and s.get("target_id")!=cur.get("target_id"))})
out=root/"logs"/"cp1-alias-restart.json";out.write_text(json.dumps(rows,ensure_ascii=False,indent=2),encoding="utf-8")
if fail:print("ALIAS_RESTART_FAIL",",".join(fail));raise SystemExit(97)
if not rows:print("ALIAS_RESTART_FAIL no aliases");raise SystemExit(98)
print("ALIAS_RESTART_EVIDENCE_PASS",len(rows))
