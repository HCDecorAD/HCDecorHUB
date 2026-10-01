import json,time,pathlib,uiautomation as a
from src.adapters.uia_tab_inspector import clean_title
from src.adapters.live_identity import CID
from src.adapters.uia_activity import selected_activity
from src.core.state_fusion import StateFusion
ALLOW=("ChatGPT HCDecor V9","HC AutoDebug","HC Agent Control","HC Video Download","HC Insight","HC Design AI Studio");F=StateFusion()
def walk(x,d=0):
 if d>12:return
 for y in x.GetChildren():yield y;yield from walk(y,d+1)
def addr(w):
 for c in walk(w):
  if c.ControlTypeName=="EditControl" and (c.AutomationId or "")=="view_1017":
   try:return c.GetValuePattern().Value
   except:return ""
 return ""
w=next(x for x in a.GetRootControl().GetChildren() if "Microsoft" in (x.Name or "") and "Edge" in (x.Name or "") and "about:blank" not in (x.Name or ""))
tabs=[x for x in walk(w) if x.ControlTypeName=="TabItemControl"];orig=next((x for x in tabs if x.GetSelectionItemPattern().IsSelected),None);rows=[]
try:
 for t in tabs:
  title=clean_title(t.Name)
  if not any(x.lower() in title.lower() for x in ALLOW):continue
  t.GetSelectionItemPattern().Select();url="";m=None
  for _ in range(12):
   time.sleep(.25);url=addr(w);m=CID.search(url or "")
   if m:break
  facts=selected_activity(w.Name);facts["identity_exact"]=bool(m);facts["identity_age_seconds"]=0 if m else None
  rows.append({"title":title,"conversation_id":m.group(1).lower() if m else None,"state":F.classify(facts),"facts":facts})
finally:
 if orig:orig.GetSelectionItemPattern().Select()
print(json.dumps({"restored":clean_title(orig.Name) if orig else None,"rows":rows},ensure_ascii=False,indent=2))
