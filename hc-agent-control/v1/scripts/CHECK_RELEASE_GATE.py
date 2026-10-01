import json,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1];d=json.loads((root/"release-gates.json").read_text(encoding="utf-8"))
ok={"PASS","PASS_LOCAL","PASS_STAGED"}
blocked=[]
for g in d["required_for_v1_production"]:
 s=d["gates"].get(g,"MISSING")
 if s not in ok:blocked.append((g,s))
if blocked:
 print("V1_PRODUCTION_BLOCKED")
 for g,s in blocked:print(g,s)
 raise SystemExit(40)
print("V1_PRODUCTION_GATE_PASS")
