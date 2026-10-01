import uiautomation as auto
def _walk(c,depth=0,maxdepth=12):
 if depth>maxdepth:return
 for x in c.GetChildren():
  yield x
  yield from _walk(x,depth+1,maxdepth)
def inspect_edge():
 out=[]
 for w in auto.GetRootControl().GetChildren():
  name=w.Name or ""
  if "Microsoft" not in name or "Edge" not in name:continue
  tabs=[];urls=[]
  for c in _walk(w):
   try:
    if c.ControlTypeName=="TabItemControl":tabs.append({"title":c.Name or "","selected":bool(getattr(c,"IsSelected",False))})
    aid=(c.AutomationId or "").lower(); n=(c.Name or "").lower()
    if aid=="addresseditbox" or "address and search" in n:
     try:v=c.GetValuePattern().Value
     except Exception:v=""
     if v:urls.append(v)
   except Exception:pass
  out.append({"window":name,"tabs":tabs,"selected_url":urls[0] if urls else None})
 return out
