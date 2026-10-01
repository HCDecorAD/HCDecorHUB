import time
class SupervisorPolicy:
 def __init__(self,cooldown=300,max_continues=3):
  self.cooldown=float(cooldown);self.max_continues=int(max_continues);self.sent={}
 def choose(self,page,state,now=None,limited=False):
  now=time.time() if now is None else float(now)
  cid=str(page.get("conversation_id") or ""); title=str(page.get("title") or "")
  if not cid:return {"action":"SKIP","reason":"NO_CID"}
  if limited:return {"action":"HANDOFF_NEW_CHAT","reason":"CHAT_LIMIT_CONFIRMED","source_cid":cid,"title":title}
  if state in ("WORKING","WAITING","BLOCKED","OFFLINE","LOST","UNKNOWN"):return {"action":"SKIP","reason":state}
  if state not in ("READY","IDLE"):return {"action":"SKIP","reason":"UNSUPPORTED_STATE"}
  x=self.sent.get(cid,{"at":0.0,"count":0})
  if now-x["at"]<self.cooldown:return {"action":"SKIP","reason":"COOLDOWN"}
  if x["count"]>=self.max_continues:return {"action":"REVIEW_REQUIRED","reason":"CONTINUE_LIMIT"}
  return {"action":"CONTINUE_EXISTING","conversation_id":cid,"title":title}
 def mark_sent(self,cid,now=None):
  now=time.time() if now is None else float(now);x=self.sent.get(cid,{"at":0.0,"count":0});self.sent[cid]={"at":now,"count":x["count"]+1}
