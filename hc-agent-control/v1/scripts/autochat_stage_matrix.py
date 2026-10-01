import json,pathlib,datetime
root=pathlib.Path(__file__).resolve().parents[1]
def ok(p):return p.exists()
m={
"A1_Detect":ok(root/"scripts/11_CAPTURE_CP1_EVIDENCE.bat"),
"A2_Alias":ok(root/"src/core/registry.py"),
"A3_UI":ok(root/"src/ui/app.py"),
"A4_Transport":ok(root/"src/adapters/cdp_input.py"),
"A4_ExactTransaction":ok(root/"src/core/live_transaction.py"),
"A5_Verify":ok(root/"src/core/postverify.py"),
"A6_Restart":ok(root/"scripts/A6_RESTART_ACCEPTANCE.bat"),
"A7_Package":ok(root/"scripts/A7_PACKAGE_PUBLIC_RC.bat"),
"PUBLIC_Gate":ok(root/"scripts/AUTOCHAT_PUBLIC_GATE.bat")}
row={"schema":"hc-autochat-stage-matrix/v1","ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"stages":m,"live_verified":False}
(root/"logs").mkdir(exist_ok=True);(root/"logs"/"autochat-stage-matrix.json").write_text(json.dumps(row,indent=2),encoding="utf-8")
print(json.dumps(row,indent=2))
