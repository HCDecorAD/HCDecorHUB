from .verifier import TargetVerifier
class DispatchBlocked(RuntimeError):pass
class Dispatcher:
    def __init__(self,adapter,queue=None,audit=None,armed=False):self.adapter=adapter;self.queue=queue;self.audit=audit;self.armed=armed
    def dispatch(self,expected,item):
        alias=item["alias"].upper()
        if self.queue and (self.queue.global_paused or alias in self.queue.paused_aliases):raise DispatchBlocked("dispatch paused")
        current=self.adapter.inspect(expected)
        verification=TargetVerifier.verify(expected,current)
        if not self.armed:
            if self.audit:self.audit.write("dispatch_dry_run",alias=alias,command_id=item["id"])
            return {"ok":True,"dry_run":True,"verification":verification}
        receipt=self.adapter.send(current,item["text"],item["idempotency_key"])
        if self.audit:self.audit.write("dispatch_sent",alias=alias,command_id=item["id"])
        return {"ok":True,"dry_run":False,"receipt":receipt,"verification":verification}
