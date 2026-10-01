class TransitionTracker:
 def __init__(self):self.last={}
 def update(self,current):
  events=[]
  for key,state in current.items():
   prev=self.last.get(key)
   if prev and prev!=state:events.append({"id":key,"from":prev,"to":state})
   self.last[key]=state
  for key in list(self.last):
   if key not in current and self.last[key]!="OFFLINE":
    events.append({"id":key,"from":self.last[key],"to":"OFFLINE"});self.last[key]="OFFLINE"
  return events
