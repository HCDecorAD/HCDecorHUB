import json,hashlib
from pathlib import Path
root=Path(r"D:\HCDecorHUB\repos\HCDecorHUB")
mp=root/"wordpress/hcdecor-sync-manifest.json"
m=json.loads(mp.read_text(encoding="utf-8-sig"))
m["version"]="2026.09.30.301"
rels=["hcdecor-runtime.php","modules/iam-runtime.php"]
by={x["path"]:x for x in m["files"]}
for rel in rels:
 b=(root/"wordpress/hcdecor-core"/rel).read_bytes()
 sha=hashlib.sha1(b"blob "+str(len(b)).encode()+b"\0"+b).hexdigest()
 url="https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress/hcdecor-core/"+rel
 if rel in by: by[rel].update(url=url,git_sha1=sha)
 else: m["files"].append({"path":rel,"url":url,"git_sha1":sha})
mp.write_text(json.dumps(m,ensure_ascii=False,indent=2)+"\n",encoding="utf-8")
print(m["version"],len(m["files"]))
