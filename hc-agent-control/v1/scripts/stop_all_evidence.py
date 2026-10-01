import json,pathlib,datetime
root=pathlib.Path(__file__).resolve().parents[1];rid=""
try:rid=json.loads((root/"runtime"/"acceptance-run.json").read_text(encoding="utf-8")).get("run_id","")
except Exception:pass
arm=root/"runtime"/"G2_LIVE_ARM.json";flag=root/"runtime"/"STOP_ALL.flag"
row={"schema":"hc-autochat-stop-all/v1","run_id":rid,"ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"pass":flag.exists() and not arm.exists(),"stop_flag":flag.exists(),"live_arm_absent":not arm.exists()}
(root/"logs").mkdir(exist_ok=True);(root/"logs"/"stop-all-evidence.json").write_text(json.dumps(row,indent=2),encoding="utf-8")
print("STOP_ALL_EVIDENCE_PASS" if row["pass"] else "STOP_ALL_EVIDENCE_FAIL");raise SystemExit(0 if row["pass"] else 1)
