import json,pathlib
class RegistryConflict(RuntimeError):pass
class ChatRegistry:
 def __init__(self,path):self.path=pathlib.Path(path);self.data={"version":1,"chats":{}};self.load()
 def load(self):
  if self.path.exists():
   try:self.data=json.loads(self.path.read_text(encoding="utf-8"))
   except Exception:self.data={"version":1,"chats":{}}
  return self.data
 def save(self):
  self.path.parent.mkdir(parents=True,exist_ok=True);tmp=self.path.with_suffix(self.path.suffix+".tmp");tmp.write_text(json.dumps(self.data,ensure_ascii=False,indent=2),encoding="utf-8");tmp.replace(self.path)
 def bind(self,alias,page,replace=False):
  cid=str(page.get("conversation_id") or "").strip()
  if not cid:raise ValueError("conversation_id required")
  a=alias.upper();existing=self.data["chats"].get(a)
  if existing and existing.get("conversation_id")!=cid and not replace:raise RegistryConflict("alias already bound")
  for other,item in self.data["chats"].items():
   if other!=a and item.get("conversation_id")==cid:raise RegistryConflict("conversation already bound")
  self.data["chats"][a]={"alias":a,"conversation_id":cid,"url":page.get("url",""),"title":page.get("title",""),"target_id":page.get("target_id",""),"adapter":page.get("adapter","cdp")};self.save();return self.data["chats"][a]
 def unbind(self,alias):self.data["chats"].pop(alias.upper(),None);self.save()
 def get(self,alias):return self.data["chats"].get(alias.upper())
 def all(self):return list(self.data["chats"].values())
