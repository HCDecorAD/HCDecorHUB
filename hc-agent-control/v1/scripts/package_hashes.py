import hashlib,json,pathlib,datetime,sys
root=pathlib.Path(__file__).resolve().parents[1];target=pathlib.Path(sys.argv[1]) if len(sys.argv)>1 else root/"dist"/"HC-Agent-Control-V1-Staging"
if not target.is_absolute():target=root/target
rows=[]
if target.is_file():files=[target]
elif target.exists():files=[p for p in target.rglob("*") if p.is_file()]
else:print("HASH_TARGET_MISSING",target);raise SystemExit(140)
for p in sorted(files):
 h=hashlib.sha256(p.read_bytes()).hexdigest();rows.append({"path":str(p.relative_to(target.parent)).replace("\\","/"),"sha256":h,"size":p.stat().st_size})
out=root/"logs"/"package-hashes.json";out.parent.mkdir(parents=True,exist_ok=True);out.write_text(json.dumps({"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"target":str(target),"files":rows},indent=2),encoding="utf-8")
print("PACKAGE_HASH_PASS",len(rows),out)
