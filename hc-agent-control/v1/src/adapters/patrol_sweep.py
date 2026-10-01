import time,uiautomation as auto
from src.adapters.uia_tab_inspector import clean_title
from src.adapters.live_identity import CID
from src.adapters.uia_activity import selected_activity
def _walk(c,d=0):
 if d>12:return
 for x in c.GetChildren():yield x;yield from _walk(x,d+1)
def _url(w):
 for c in _walk(w):
  if c.ControlTypeName=="EditControl" and (c.AutomationId or "")=="view_1017":
   try:return c.GetValuePattern().Value
   except Exception:return ""
 return ""
def _selected_title(tabs):
 for t in tabs:
  try:
   if t.GetSelectionItemPattern().IsSelected:return clean_title(t.Name)
  except Exception:pass
 return None
def sweep_window(window_name,allowed):
 w=next((x for x in auto.GetRootControl().GetChildren() if (x.Name or "")==window_name),None)
 if not w:return []
 tabs=[x for x in _walk(w) if x.ControlTypeName=="TabItemControl"];orig=next((x for x in tabs if x.GetSelectionItemPattern().IsSelected),None);rows=[]
 try:
  for t in tabs:
   title=clean_title(t.Name)
   if not allowed(title):continue
   t.GetSelectionItemPattern().Select();m=None;url="";stable=0;last=None
   for _ in range(16):
    time.sleep(.25);sel=_selected_title(tabs);url=_url(w);cur=CID.search(url or "")
    key=(sel,url)
    stable=stable+1 if key==last else 1;last=key
    if sel==title and cur and stable>=2:m=cur;break
   facts=selected_activity(window_handle=w.NativeWindowHandle);rows.append({"title":title,"conversation_id":m.group(1).lower() if m else None,"url":url if m else None,"identity_exact":bool(m),"identity_title_match":_selected_title(tabs)==title,"facts":facts})
 finally:
  if orig:orig.GetSelectionItemPattern().Select()
 return rows
