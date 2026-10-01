import json,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1]
selftest=root/"logs"/"cp5-exe-selftest.json";checkpoint=root/"checkpoints"/"CP5_PACKAGE_SMOKE.json"
fail=[]
try:s=json.loads(selftest.read_text(encoding="utf-8"))
except Exception as e:s={};fail.append("selftest_missing_or_invalid")
if s.get("schema")!="hc-agent-control-exe-selftest/v1" or s.get("ok") is not True:fail.append("exe_selftest_not_pass")
try:c=json.loads(checkpoint.read_text(encoding="utf-8"))
except Exception:c={};fail.append("checkpoint_missing_or_invalid")
if fail:print("CP5_EVIDENCE_FAIL",",".join(fail));raise SystemExit(300)
print("CP5_EVIDENCE_VALID")
