class AlertTracker:
 def __init__(self):self.previous={}
 def transitions(self,current):
  out=[]
  for alias,state in current.items():
   old=self.previous.get(alias)
   if old!=state and state in ("LOST","OFFLINE","ERROR"):out.append({"alias":alias,"from":old,"to":state})
  self.previous=dict(current);return out
