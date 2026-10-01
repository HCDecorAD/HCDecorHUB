import time
class RecoveryMonitor:
 def __init__(self,discovery,ensure=None,attempts=3,delay=0.2):self.discovery=discovery;self.ensure=ensure;self.attempts=attempts;self.delay=delay
 def probe(self):
  try:return {"connection":"ONLINE","pages":self.discovery.pages(),"recovered":False}
  except Exception as first:
   if not self.ensure:return {"connection":"OFFLINE","pages":[],"error":str(first),"recovered":False}
   try:self.ensure()
   except Exception as e:return {"connection":"OFFLINE","pages":[],"error":str(e),"recovered":False}
   for _ in range(self.attempts):
    try:return {"connection":"ONLINE","pages":self.discovery.pages(),"recovered":True}
    except Exception as e:last=e;time.sleep(self.delay)
   return {"connection":"LOST","pages":[],"error":str(last),"recovered":False}
