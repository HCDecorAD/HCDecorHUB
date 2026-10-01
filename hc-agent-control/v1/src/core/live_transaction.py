import time
from src.adapters.focus_contract import FocusContract
from src.adapters.cdp_input import CDPInput
from src.core.postverify import PostSubmitVerifier
class LiveTransaction:
 def __init__(self,dom):self.dom=dom
 def execute(self,page,cid,text,timeout=12):
  before=self.dom.snapshot(page)
  if before.get("conversation_id")!=cid:raise RuntimeError("identity mismatch before input")
  FocusContract.focus(page)
  CDPInput(page).type_and_enter(text)
  end=time.time()+timeout;after={}
  while time.time()<end:
   time.sleep(.4);after=self.dom.snapshot(page)
   if int(after.get("user_message_count",0))>int(before.get("user_message_count",0)):break
  return PostSubmitVerifier.verify(cid,before,after,text)
