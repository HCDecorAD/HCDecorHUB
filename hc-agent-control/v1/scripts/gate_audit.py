import json,pathlib
root=pathlib.Path(__file__).resolve().parents[1];g=json.loads((root/"release-gates.json").read_text(encoding="utf-8"))
allowed={"PASS","PASS_STAGED","STAGED","OPEN","LOCKED","MISSING","FAILED","BLOCKED"}
errors=[]
for name,state in g.get("gates",{}).items():
 if state not in allowed:errors.append(f"{name}:{state}")
for name in g.get("required_for_v1_production",[]):
 if name not in g.get("gates",{}):errors.append("required_missing:"+name)
if errors:print("GATE_AUDIT_FAIL",errors);raise SystemExit(310)
prod=all(g["gates"].get(x)=="PASS" for x in g["required_for_v1_production"])
print("GATE_AUDIT_PASS","PRODUCTION_READY" if prod else "PRODUCTION_BLOCKED")
