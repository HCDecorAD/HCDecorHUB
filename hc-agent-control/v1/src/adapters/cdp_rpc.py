import json,threading
class CDPRPC:
 def __init__(self,url,timeout=3):
  self.url=url;self.timeout=timeout;self.seq=0
  try:import websocket
  except ImportError as e:raise RuntimeError("websocket-client unavailable") from e
  self.ws=websocket.create_connection(url,timeout=timeout)
 def close(self):
  try:self.ws.close()
  except Exception:pass
 def call(self,method,params=None):
  self.seq+=1;i=self.seq;self.ws.send(json.dumps({"id":i,"method":method,"params":params or {}}))
  while True:
   d=json.loads(self.ws.recv())
   if d.get("id")==i:
    if "error" in d:raise RuntimeError(str(d["error"]))
    return d.get("result",{})
