import json,pathlib,datetime,subprocess,sys
root=pathlib.Path(__file__).resolve().parents[1]
checks=[
 ("ui_runtime",["python","scripts/ui_runtime_probe.py"]),
 ("ui_policy",["python","-m","unittest","tests.test_ui_policy_v1","tests.test_daily_ui_policy_v1","tests.test_first_run_policy_v1"]),
 ("status",["python","-m","unittest","tests.test_settings_v1","tests.test_heartbeat_v1","tests.test_live_status_v1"]),
 ("cdp_readonly",["python","-m","unittest","tests.test_cdp_readonly_v1","tests.test_activity_v1"]),
 ("package_policy",["python","-m","unittest","tests.test_package_policy_v1"]),
]
rows=[]
for name,cmd in checks:
 p=subprocess.run(cmd,cwd=root,capture_output=True,text=True)
 rows.append({"name":name,"pass":p.returncode==0,"code":p.returncode,"output":(p.stdout+p.stderr)[-4000:]})
report={"schema":"hcac-acceptance/v1","created_at":datetime.datetime.now(datetime.timezone.utc).isoformat(),"real_send":"OFF","checks":rows,"pass":all(x["pass"] for x in rows)}
out=root/"logs"/"acceptance-report.json";out.parent.mkdir(exist_ok=True);out.write_text(json.dumps(report,indent=2),encoding="utf-8")
print("ACCEPTANCE_REPORT_PASS" if report["pass"] else "ACCEPTANCE_REPORT_FAIL",out)
raise SystemExit(0 if report["pass"] else 220)
