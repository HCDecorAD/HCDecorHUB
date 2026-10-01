from .verifier import TargetVerifier,TargetMismatch
class DispatchBlocked(RuntimeError):pass
class Dispatcher:
 def __init__(self,adapter,queue=None,audit=None,armed=False):self.adapter=adapter;self.queue=queue;self.audit=audit;self.armed=armed
 def _audit(self,event,**data):
  if self.audit:self.audit.write(event,**data)
 def dispatch(self,expected,item):
  alias=item["alias"].upper()
  if self.queue and (self.queue.global_paused or alias in self.queue.paused_aliases):
   self._audit("dispatch_abort",alias=alias,command_id=item["id"],reason="paused");raise DispatchBlocked("dispatch paused")
  try:
   current=self.adapter.inspect(expected);verification=TargetVerifier.verify(expected,current)
  except Exception as e:
   self._audit("dispatch_abort",alias=alias,command_id=item["id"],reason=str(e));raise
  if not self.armed:
   self._audit("dispatch_dry_run",alias=alias,command_id=item["id"],conversation_id=verification["conversation_id"])
   return {"ok":True,"dry_run":True,"verification":verification}
  if self.queue:self.queue.set_state(item["id"],"RUNNING")
  try:
   receipt=self.adapter.send(current,item["text"],item["idempotency_key"])
   if self.queue:self.queue.set_state(item["id"],"PASS")
   self._audit("dispatch_sent",alias=alias,command_id=item["id"],conversation_id=verification["conversation_id"])
   return {"ok":True,"dry_run":False,"receipt":receipt,"verification":verification}
  except Exception as e:
   if self.queue:self.queue.set_state(item["id"],"FAILED")
   self._audit("dispatch_failed",alias=alias,command_id=item["id"],reason=str(e));raise
