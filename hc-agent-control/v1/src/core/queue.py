import json,pathlib,uuid
from datetime import datetime,timezone

class CommandQueue:
    def __init__(self,path=None):
        self.path=pathlib.Path(path) if path else None
        self.items=[];self.global_paused=False;self.paused_aliases=set();self.keys=set()
        if self.path and self.path.exists(): self._load()
    def _load(self):
        try:
            d=json.loads(self.path.read_text(encoding="utf-8"));self.items=d.get("items",[]);self.global_paused=bool(d.get("global_paused"));self.paused_aliases=set(d.get("paused_aliases",[]));self.keys={x.get("idempotency_key") for x in self.items if x.get("idempotency_key")}
        except Exception: self.items=[]
    def _save(self):
        if not self.path:return
        self.path.parent.mkdir(parents=True,exist_ok=True);tmp=self.path.with_suffix(self.path.suffix+".tmp");tmp.write_text(json.dumps({"items":self.items,"global_paused":self.global_paused,"paused_aliases":sorted(self.paused_aliases)},ensure_ascii=False,indent=2),encoding="utf-8");tmp.replace(self.path)
    def enqueue(self,alias,text,idempotency_key=None):
        alias=alias.upper();key=idempotency_key or str(uuid.uuid4())
        if key in self.keys:return next(x for x in self.items if x.get("idempotency_key")==key)
        item={"id":str(uuid.uuid4()),"idempotency_key":key,"alias":alias,"text":text,"created_at":datetime.now(timezone.utc).isoformat(),"state":"READY"};self.items.append(item);self.keys.add(key);self._save();return item
    def next(self,alias):
        alias=alias.upper()
        if self.global_paused or alias in self.paused_aliases:return None
        return next((x for x in self.items if x["alias"]==alias and x["state"] in ("READY","RETRY")),None)
    def set_state(self,item_id,state):
        for x in self.items:
            if x["id"]==item_id:x["state"]=state;self._save();return x
        raise KeyError(item_id)
    def pause(self,alias=None):
        if alias:self.paused_aliases.add(alias.upper())
        else:self.global_paused=True
        self._save()
    def resume(self,alias=None):
        if alias:self.paused_aliases.discard(alias.upper())
        else:self.global_paused=False
        self._save()
    def stop_all(self):self.pause()
