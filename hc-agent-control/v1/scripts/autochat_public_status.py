import json,pathlib,datetime
root=pathlib.Path(__file__).resolve().parents[1]
checks={
"A1_detect":(root/"logs"/"cp1-real-cdp.json").exists(),
"A2_alias":(root/"data"/"chats.json").exists(),
"A3_ui":(root/"src"/"ui"/"app.py").exists(),
"A4_exact_send":False,
"A5_verify":False,
"A6_restart":(root/"logs"/"cp1-alias-restart.json").exists(),
"A7_package":(root/"dist"/"HC-Agent-Control-V1-EarlyUse").exists(),
}
row={"schema":"hc-autochat-public-status/v1","ts":datetime.datetime.now(datetime.timezone.utc).isoformat(),"checks":checks,"public_ready":all(checks.values())}
(root/"logs").mkdir(exist_ok=True);(root/"logs"/"autochat-public-status.json").write_text(json.dumps(row,indent=2),encoding="utf-8")
print(json.dumps(row,indent=2));raise SystemExit(0 if row["public_ready"] else 350)
