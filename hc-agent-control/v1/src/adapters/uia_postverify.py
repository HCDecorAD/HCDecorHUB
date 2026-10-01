import time
def user_texts(walk,window):
 out=[]
 for c in walk(window):
  name=(c.Name or "").strip()
  if name and c.ControlTypeName in ("TextControl","DocumentControl") and len(name)>3:out.append(name)
 return out
def verify_new_exact(walk,window,text,before,timeout=8):
 end=time.time()+timeout
 while time.time()<end:
  time.sleep(.4);now=user_texts(walk,window)
  if len(now)>len(before) and text in now:return True
 return False
