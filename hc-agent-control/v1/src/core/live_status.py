import datetime
class LiveStatusTracker:
 def __init__(self):self.seen={}
 def update(self,pages,now=None):
  now=now or datetime.datetime.now(datetime.timezone.utc);out={}
  for p in pages:
   cid=p.get("conversation_id")
   if not cid:continue
   prev=self.seen.get(cid);changed=bool(prev and prev.get("target_id")!=p.get("target_id"))
   self.seen[cid]={"target_id":p.get("target_id"),"seen":now}
   out[cid]={"state":"ONLINE","seen":now.isoformat(),"target_changed":changed}
  return out
 def state(self,cid,now=None):
  now=now or datetime.datetime.now(datetime.timezone.utc);x=self.seen.get(cid)
  if not x:return "OFFLINE"
  age=(now-x["seen"]).total_seconds()
  if age>120:return "LOST"
  if age>30:return "WAITING"
  return "IDLE"
