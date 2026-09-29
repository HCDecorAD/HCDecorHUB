import json,hashlib
from pathlib import Path
root=Path(r"D:\HCDecorHUB\repos\HCDecorHUB")
base=root/"wordpress/hcdecor-core"; mp=root/"wordpress/hcdecor-sync-manifest.json"
m=json.loads(mp.read_text(encoding="utf-8-sig")); m["version"]="2026.09.30.301"
for item in m["files"]:
 rel=item["path"]; f=base/rel
 if not f.exists(): continue
 b=f.read_bytes(); item["git_sha1"]=hashlib.sha1(b"blob "+str(len(b)).encode()+b"\0"+b).hexdigest()
 item["url"]="https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress/hcdecor-core/"+rel
rel="modules/iam-runtime.php"
if not any(x["path"]==rel for x in m["files"]):
 b=(base/rel).read_bytes();m["files"].append({"path":rel,"url":"https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress/hcdecor-core/"+rel,"git_sha1":hashlib.sha1(b"blob "+str(len(b)).encode()+b"\0"+b).hexdigest()})
mp.write_text(json.dumps(m,ensure_ascii=False,indent=2)+"\n",encoding="utf-8");print(m["version"],len(m["files"]))
