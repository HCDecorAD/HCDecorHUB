class SendAdapterContract:
 def inspect(self,expected):raise NotImplementedError
 def send(self,target,text,idempotency_key):raise NotImplementedError
 def post_state(self,target):raise NotImplementedError
