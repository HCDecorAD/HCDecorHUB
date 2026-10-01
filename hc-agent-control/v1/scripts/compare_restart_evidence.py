import json,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1]
a=json.loads((root/"logs"/sys.argv[1]).read_text());b=json.loads((root/"logs"/sys.argv[2]).read_text())
changed=a.get("websocket")!=b.get("websocket") or a.get("targets")!=b.get("targets")
if not changed:print("RESTART_EVIDENCE_FAIL identity unchanged");raise SystemExit(280)
print("RESTART_EVIDENCE_PASS")
