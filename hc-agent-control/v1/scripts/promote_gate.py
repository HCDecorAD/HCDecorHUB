import json,pathlib,sys,hashlib
from gate_evidence import validate
root=pathlib.Path(__file__).resolve().parents[1];gate=sys.argv[1];evidence=pathlib.Path(sys.argv[2]) if len(sys.argv)>2 else None
allowed={"CP1_LIVE_MULTI_CHAT","CP1_ALIAS_RESTART","G2_REAL_TARGETED_SEND","CP3_UI","CP4_RECOVERY","CP5_PACKAGE_SMOKE"}
if gate not in allowed:print("GATE_PROMOTION_DENIED",gate);raise SystemExit(93)
if not evidence or not evidence.exists():print("GATE_PROMOTION_DENIED evidence missing");raise SystemExit(94)
try:d_ev=json.loads(evidence.read_text(encoding="utf-8"))
except Exception:print("GATE_PROMOTION_DENIED invalid evidence");raise SystemExit(95)
if d_ev.get("schema")!="hc-agent-control-checkpoint/v1" or d_ev.get("name")!=gate:print("GATE_PROMOTION_DENIED evidence identity mismatch");raise SystemExit(96)
evpath=(root/str(d_ev.get("evidence") or "")) if d_ev.get("evidence") and not pathlib.Path(str(d_ev.get("evidence"))).is_absolute() else pathlib.Path(str(d_ev.get("evidence"))) if d_ev.get("evidence") else None
expected=d_ev.get("evidence_sha256")
if expected:
 if not evpath or not evpath.exists() or hashlib.sha256(evpath.read_bytes()).hexdigest()!=expected:print("GATE_PROMOTION_DENIED evidence hash mismatch");raise SystemExit(99)
try:strong=validate(root,gate,d_ev)
except Exception as e:print("GATE_PROMOTION_DENIED evidence validation error",e);raise SystemExit(97)
if not strong:print("GATE_PROMOTION_DENIED strong evidence required",gate);raise SystemExit(98)
p=root/"release-gates.json";d=json.loads(p.read_text(encoding="utf-8"));d["gates"][gate]="PASS";p.write_text(json.dumps(d,ensure_ascii=False,indent=2),encoding="utf-8");print("GATE_PROMOTED",gate)
