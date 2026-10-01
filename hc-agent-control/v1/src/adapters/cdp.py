import json,re,urllib.request
class CDPDiscovery:
 def __init__(self,host="127.0.0.1",port=9222,timeout=2):self.base=f"http://{host}:{port}";self.timeout=timeout
 def pages(self):
  with urllib.request.urlopen(self.base+"/json",timeout=self.timeout) as r:data=json.load(r)
  out=[]
  for x in data:
   if x.get("type")!="page":continue
   url=x.get("url","")
   if "chatgpt.com" not in url and "chat.openai.com" not in url:continue
   m=re.search(r"/c/([^/?#]+)",url)
   out.append({"target_id":x.get("id",""),"title":x.get("title",""),"url":url,"conversation_id":m.group(1) if m else "","websocket":x.get("webSocketDebuggerUrl",""),"adapter":"cdp"})
  return out
