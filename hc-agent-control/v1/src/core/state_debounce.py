class StateDebouncer:
 def __init__(self,required=2):self.required=required;self.last={}
 def update(self,cid,state):
  prev,count=self.last.get(cid,(None,0));count=count+1 if prev==state else 1;self.last[cid]=(state,count)
  if state=="READY" and count<self.required:return "READY_PENDING"
  if state=="READY":return "READY_STABLE"
  return state
