import re,uiautomation as auto
def _walk(c,d=0):
 if d>12:return
 for x in c.GetChildren():yield x;yield from _walk(x,d+1)
def clean_title(s):
 return re.split(r"\s+-\s+(?:Đang ngủ|Sleeping|Mức sử dụng bộ nhớ|Memory usage)",s or "",maxsplit=1)[0].strip()
def inspect_edge():
 out=[]
 for w in auto.GetRootControl().GetChildren():
  name=w.Name or ""
  if "Edge" not in name:continue
  tabs=[]
  for c in _walk(w):
   if c.ControlTypeName!="TabItemControl":continue
   try:sel=bool(c.GetSelectionItemPattern().IsSelected)
   except Exception:sel=False
   tabs.append({"title":clean_title(c.Name),"raw_title":c.Name or "","selected":sel})
  out.append({"window":name,"hwnd":w.NativeWindowHandle,"tabs":tabs,"selected_tab":next((t["title"] for t in tabs if t["selected"]),None)})
 return out
