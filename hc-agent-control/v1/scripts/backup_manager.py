import argparse,pathlib,shutil,datetime,json
root=pathlib.Path(__file__).resolve().parents[1]
ap=argparse.ArgumentParser();ap.add_argument("kind",choices=["upgrade","user"]);a=ap.parse_args()
stamp=datetime.datetime.now().astimezone().strftime("%Y%m%d-%H%M%S")
out=root/"backups"/(("upgrade-baseline-" if a.kind=="upgrade" else "user-state-")+stamp);out.mkdir(parents=True,exist_ok=False)
if a.kind=="upgrade":
 for name in ("src","scripts","tests","docs"):
  p=root/name
  if p.exists():shutil.copytree(p,out/name)
 for name in ("manifest.json","release-gates.json","HC_AutoChat.bat"):
  p=root/name
  if p.exists():shutil.copy2(p,out/name)
 (out/"UPGRADE-BASELINE.txt").write_text("HC Agent Control V1 upgrade baseline.\n",encoding="utf-8")
else:
 p=root/"data"
 if p.exists():shutil.copytree(p,out/"data")
 for name in ("status-report.json","health-summary.json"):
  p=root/"logs"/name
  if p.exists():shutil.copy2(p,out/name)
(out/"backup-meta.json").write_text(json.dumps({"kind":a.kind,"created":datetime.datetime.now(datetime.timezone.utc).isoformat()},indent=2),encoding="utf-8")
print(("UPGRADE_BASELINE_BACKUP_PASS" if a.kind=="upgrade" else "USER_STATE_BACKUP_PASS"),out)
