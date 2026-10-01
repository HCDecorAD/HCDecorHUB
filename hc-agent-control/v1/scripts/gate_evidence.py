import json,pathlib
def load(p):return json.loads(pathlib.Path(p).read_text(encoding="utf-8"))
def validate(root,gate,checkpoint):
 ev=str(checkpoint.get("evidence") or "");p=(root/ev) if ev and not pathlib.Path(ev).is_absolute() else pathlib.Path(ev)
 if gate=="CP1_LIVE_MULTI_CHAT":
  d=load(p);ids={str(x.get("conversation_id") or "") for x in d if x.get("conversation_id")}
  return len(ids)>=2
 if gate=="CP1_ALIAS_RESTART":
  d=load(p);return bool(d) and all(x.get("conversation_id") and x.get("current_target_id") for x in d)
 if gate=="CP4_RECOVERY":
  d=load(p);return d.get("required_down") is True and d.get("before",{}).get("alive") is False and d.get("after",{}).get("alive") is True and d.get("pass") is True
 if gate=="CP5_PACKAGE_SMOKE":
  s=load(root/"logs"/"cp5-exe-selftest.json");return s.get("schema")=="hc-agent-control-exe-selftest/v1" and s.get("ok") is True
 if gate=="G2_REAL_TARGETED_SEND":
  return False
 return True
