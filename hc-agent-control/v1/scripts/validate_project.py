import ast,json,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1];errors=[]
required=["manifest.json","release-gates.json","HC_AutoChat.bat","src/core/verifier.py","src/core/queue.py","src/core/registry.py","src/core/resolver.py","src/core/recovery.py","src/ui/app.py","src/adapters/cdp.py"]
for rel in required:
 if not (root/rel).exists():errors.append("missing "+rel)
for p in (root/"src").rglob("*.py"):
 try:ast.parse(p.read_text(encoding="utf-8"),filename=str(p))
 except Exception as e:errors.append(f"syntax {p.relative_to(root)} {e}")
for rel in ("manifest.json","release-gates.json"):
 try:json.loads((root/rel).read_text(encoding="utf-8"))
 except Exception as e:errors.append(f"json {rel} {e}")
if errors:
 print("PROJECT_VALIDATION_FAIL");print("\n".join(errors));raise SystemExit(81)
print("PROJECT_VALIDATION_PASS")
