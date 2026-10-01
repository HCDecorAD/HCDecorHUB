import time,pyperclip
from src.adapters.live_identity import CID
def composer_copy(auto,field):
 old=pyperclip.paste()
 try:
  field.SetFocus();auto.SendKeys("{Ctrl}a",waitTime=.03);auto.SendKeys("{Ctrl}c",waitTime=.03);time.sleep(.12);return pyperclip.paste()
 finally:pyperclip.copy(old)
def verify_submit(auto,walk,url_fn,window,field,cid,command,timeout=5):
 end=time.time()+timeout
 while time.time()<end:
  time.sleep(.3)
  m=CID.search(url_fn(window) or "")
  if not m or m.group(1).lower()!=cid.lower():return {"verified":False,"stage":"CID_CHANGED"}
  now=composer_copy(auto,field)
  if now.strip()!=command.strip():return {"verified":True,"stage":"COMPOSER_COMMAND_CLEARED"}
 return {"verified":False,"stage":"UNCERTAIN_SIDE_EFFECT"}
