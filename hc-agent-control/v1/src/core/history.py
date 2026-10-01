import json,pathlib
def export_queue(queue,path,state=None,alias=None):
 rows=queue.items
 if state:rows=[x for x in rows if x.get("state")==state]
 if alias:rows=[x for x in rows if x.get("alias")==alias.upper()]
 p=pathlib.Path(path);p.parent.mkdir(parents=True,exist_ok=True);p.write_text(json.dumps(rows,ensure_ascii=False,indent=2),encoding="utf-8");return len(rows)
