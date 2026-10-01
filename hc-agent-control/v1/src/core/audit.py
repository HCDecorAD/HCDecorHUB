import json,pathlib
from datetime import datetime,timezone
class AuditLog:
 def __init__(self,path):self.path=pathlib.Path(path);self.path.parent.mkdir(parents=True,exist_ok=True)
 def write(self,event,**data):
  row={"ts":datetime.now(timezone.utc).isoformat(),"event":event,"data":data}
  with self.path.open("a",encoding="utf-8") as f:f.write(json.dumps(row,ensure_ascii=False)+"\n")
  return row
