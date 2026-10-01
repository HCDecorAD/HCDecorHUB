import time
from src.adapters.cdp import CDPDiscovery
from src.adapters.cdp_rpc import CDPRPC
from src.adapters.chatgpt_dom import ChatGPTDOM
from src.adapters.focus_contract import FocusContract
from src.adapters.cdp_input import CDPInput

class PublicEntryError(RuntimeError): pass

class PublicEntry:
 def __init__(self, discovery=None, dom=None):
  self.discovery=discovery or CDPDiscovery(); self.dom=dom or ChatGPTDOM()
 def _browser_ws(self):
  import json,urllib.request
  with urllib.request.urlopen(self.discovery.base+"/json/version",timeout=self.discovery.timeout) as r:d=json.load(r)
  return d["webSocketDebuggerUrl"]
 def create_target(self):
  rpc=CDPRPC(self._browser_ws())
  try:r=rpc.call("Target.createTarget",{"url":"https://chatgpt.com/"})
  finally:rpc.close()
  tid=r.get("targetId","")
  if not tid:raise PublicEntryError("new target id unavailable")
  return tid
 def _page(self,tid):
  rows=[p for p in self.discovery.pages() if p.get("target_id")==tid]
  return rows[0] if rows else None
 def new(self,title,prompt,timeout=25):
  if not str(prompt or "").strip():raise PublicEntryError("prompt is required")
  tid=self.create_target(); end=time.time()+timeout; page=None
  while time.time()<end:
   page=self._page(tid)
   if page:
    try:
     FocusContract.focus(page)
     if self.dom.composer_text(page) is not None:break
    except Exception:pass
   time.sleep(.25)
  else:raise PublicEntryError("new chat composer unavailable")
  inp=CDPInput(page); inp.insert_text(prompt)
  if str(self.dom.composer_text(page) or "").strip()!=str(prompt).strip():raise PublicEntryError("composer readback mismatch")
  snap=self.dom.snapshot(page)
  if snap.get("conversation_id"):raise PublicEntryError("target is not a fresh chat")
  inp.submit()
  before_count=int(snap.get("user_message_count",0)); after={}
  while time.time()<end:
   time.sleep(.35);page=self._page(tid) or page;after=self.dom.snapshot(page)
   cid=str(after.get("conversation_id") or "")
   canonical=bool(cid) and not cid.lower().startswith("local-chatgpt") and "%3a" not in cid.lower()
   if canonical and int(after.get("user_message_count",0))>before_count and str(after.get("last_user_text") or "").strip()==str(prompt).strip():
    return {"ok":True,"title":str(title or "").strip(),"conversation_id":cid,"url":after.get("url") or page.get("url"),"last_user_text":str(prompt).strip(),"target_id":tid}
  raise PublicEntryError("exact-send verification failed")
