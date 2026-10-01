import json,pathlib,shutil,datetime
CURRENT=1
def migrate(root):
 root=pathlib.Path(root);data=root/"data";data.mkdir(parents=True,exist_ok=True);backup=root/"backups"/"migrations";backup.mkdir(parents=True,exist_ok=True)
 changes=[]
 for name,default in (("chats.json",{"version":CURRENT,"chats":{}}),("queue.json",{"items":[],"global_paused":True,"paused_aliases":[]})):
  p=data/name
  if not p.exists():p.write_text(json.dumps(default,indent=2),encoding="utf-8");changes.append(name+":created");continue
  try:d=json.loads(p.read_text(encoding="utf-8"))
  except Exception:
   stamp=datetime.datetime.now().strftime("%Y%m%d-%H%M%S");shutil.copy2(p,backup/f"{name}.{stamp}.corrupt");p.write_text(json.dumps(default,indent=2),encoding="utf-8");changes.append(name+":repaired");continue
  if name=="chats.json":d.setdefault("version",CURRENT);d.setdefault("chats",{})
  else:d.setdefault("items",[]);d.setdefault("global_paused",True);d.setdefault("paused_aliases",[])
  p.write_text(json.dumps(d,ensure_ascii=False,indent=2),encoding="utf-8")
 return changes
