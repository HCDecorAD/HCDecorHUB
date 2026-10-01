import json,pathlib
DEFAULT={"theme":"system","first_run_done":False,"window_geometry":"1220x760","notifications":True,"auto_refresh_ms":5000}
class Settings:
 def __init__(self,path):self.path=pathlib.Path(path);self.data=dict(DEFAULT);self.load()
 def load(self):
  if self.path.exists():
   try:self.data.update(json.loads(self.path.read_text(encoding="utf-8")))
   except Exception:pass
  return self.data
 def save(self):
  self.path.parent.mkdir(parents=True,exist_ok=True);tmp=self.path.with_suffix(".tmp");tmp.write_text(json.dumps(self.data,indent=2),encoding="utf-8");tmp.replace(self.path)
 def get(self,k,default=None):return self.data.get(k,default)
 def set(self,k,v):self.data[k]=v;self.save()
