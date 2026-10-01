from .resolver import AliasResolver,AliasResolutionError
class AutoChatService:
 def __init__(self,discovery,registry,queue,dispatcher_factory):self.discovery=discovery;self.registry=registry;self.queue=queue;self.dispatcher_factory=dispatcher_factory
 def snapshot(self):
  pages=self.discovery.pages();out=[]
  for saved in self.registry.all():
   try:
    current=AliasResolver.resolve(saved,pages);state="ONLINE"
   except AliasResolutionError:
    current=None;state="OFFLINE"
   out.append({"alias":saved["alias"],"state":state,"saved":saved,"current":current})
  return out
 def dry_run(self,alias,text,key=None):
  saved=self.registry.get(alias)
  if not saved:raise KeyError("alias not registered")
  current=AliasResolver.resolve(saved,self.discovery.pages())
  item=self.queue.enqueue(alias,text,key)
  return self.dispatcher_factory(current).dispatch(saved,item)
