import json,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1];p=root/"dist"/"HC-Agent-Control-V1-Staging"
ch=json.loads((p/"data"/"chats.json").read_text(encoding="utf-8"));q=json.loads((p/"data"/"queue.json").read_text(encoding="utf-8"))
errors=[]
if ch.get("chats"):errors.append("package contains chat bindings")
if q.get("items"):errors.append("package contains queued commands")
if not q.get("global_paused"):errors.append("package is not STOP ALL by default")
for forbidden in ("logs","runtime","backups","checkpoints"):
 if (p/forbidden).exists() and any((p/forbidden).rglob("*")):errors.append("runtime artifact packaged: "+forbidden)
if errors:
 print("PACKAGE_SAFETY_FAIL",errors);raise SystemExit(201)
print("PACKAGE_SAFETY_PASS clean-registry clean-queue stopped-default")
