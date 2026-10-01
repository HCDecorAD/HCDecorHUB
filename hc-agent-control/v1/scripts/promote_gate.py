import json,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1];gate=sys.argv[1];evidence=pathlib.Path(sys.argv[2]) if len(sys.argv)>2 else None
allowed={"CP1_LIVE_MULTI_CHAT","CP1_ALIAS_RESTART","G2_REAL_TARGETED_SEND","CP3_UI","CP4_RECOVERY","CP5_PACKAGE_SMOKE"}
if gate not in allowed:print("GATE_PROMOTION_DENIED",gate);raise SystemExit(93)
if not evidence or not evidence.exists():print("GATE_PROMOTION_DENIED evidence missing");raise SystemExit(94)
try:d_ev=json.loads(evidence.read_text(encoding="utf-8"))
except Exception:print("GATE_PROMOTION_DENIED invalid evidence");raise SystemExit(95)
if d_ev.get("schema")!="hc-agent-control-checkpoint/v1" or d_ev.get("name")!=gate:
 print("GATE_PROMOTION_DENIED evidence identity mismatch");raise SystemExit(96)
p=root/"release-gates.json";d=json.loads(p.read_text(encoding="utf-8"));d["gates"][gate]="PASS";p.write_text(json.dumps(d,ensure_ascii=False,indent=2),encoding="utf-8");print("GATE_PROMOTED",gate)
