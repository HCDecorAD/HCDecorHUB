import re,uiautomation as auto
CID=re.compile(r"https?://chatgpt\.com/c/([0-9a-f-]{20,})",re.I)
def _walk(c,d=0):
 if d>12:return
 for x in c.GetChildren():yield x;yield from _walk(x,d+1)
def selected_identities():
 out=[]
 for w in auto.GetRootControl().GetChildren():
  wn=w.Name or ""
  if "Edge" not in wn and "Chrome" not in wn:continue
  title=None;url=None
  for c in _walk(w):
   try:
    if c.ControlTypeName=="TabItemControl" and c.GetSelectionItemPattern().IsSelected:title=c.Name or ""
    if c.ControlTypeName=="EditControl" and (c.AutomationId or "") in ("view_1017","view_1012"):
     v=c.GetValuePattern().Value
     if v:url=v
   except Exception:pass
  m=CID.search(url or "")
  out.append({"window":wn,"selected_title":title,"url":url,"conversation_id":m.group(1).lower() if m else None,"confidence":"EXACT_SELECTED" if m else "URL_ONLY"})
 return out
