import time,pyperclip,uiautomation as auto
from src.adapters.live_identity import CID
from src.adapters.uia_tab_inspector import clean_title
def _walk(c,d=0):
 if d>14:return
 for x in c.GetChildren():yield x;yield from _walk(x,d+1)
def _url(w):
 for c in _walk(w):
  if c.ControlTypeName=="EditControl" and (c.AutomationId or "")=="view_1017":
   try:return c.GetValuePattern().Value
   except Exception:return ""
 return ""
class UIAExactTransport:
 def send_once(self,window,title,cid,text):
  tabs=[x for x in _walk(window) if x.ControlTypeName=="TabItemControl"];orig=next((x for x in tabs if x.GetSelectionItemPattern().IsSelected),None);target=next((x for x in tabs if clean_title(x.Name)==title),None)
  if not target:return {"ok":False,"stage":"TARGET_MISSING"}
  old=pyperclip.paste()
  try:
   target.GetSelectionItemPattern().Select()
   for _ in range(12):
    time.sleep(.25);m=CID.search(_url(window) or "")
    if m and m.group(1).lower()==cid.lower():break
   else:return {"ok":False,"stage":"CID_PRECHECK"}
   fields=[c for c in _walk(window) if c.ControlTypeName=="EditControl" and (c.ClassName or "")=="Textfield" and (c.AutomationId or "")!="view_1017"]
   if len(fields)!=1:return {"ok":False,"stage":"COMPOSER_COUNT"}
   f=fields[0];f.SetFocus();auto.SendKeys("{Ctrl}a",waitTime=.03);auto.SendKeys("{Ctrl}c",waitTime=.03);time.sleep(.12)
   existing=pyperclip.paste()
   if existing.strip():return {"ok":False,"stage":"PREEXISTING_DRAFT"}
   pyperclip.copy(text);auto.SendKeys("{Ctrl}v",waitTime=.03);time.sleep(.2);auto.SendKeys("{Ctrl}a",waitTime=.03);auto.SendKeys("{Ctrl}c",waitTime=.03);time.sleep(.15)
   if pyperclip.paste()!=text:return {"ok":False,"stage":"READBACK"}
   m=CID.search(_url(window) or "")
   if not m or m.group(1).lower()!=cid.lower():return {"ok":False,"stage":"CID_RECHECK"}
   auto.SendKeys("{ENTER}",waitTime=.05)
   return {"ok":True,"stage":"SUBMITTED_UNVERIFIED"}
  finally:
   pyperclip.copy(old)
   if orig:orig.GetSelectionItemPattern().Select()
