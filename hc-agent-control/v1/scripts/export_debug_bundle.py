import pathlib,zipfile,datetime
root=pathlib.Path(__file__).resolve().parents[1];stamp=datetime.datetime.now().strftime("%Y%m%d-%H%M%S");out=root/"dist"/f"HCAC-debug-{stamp}.zip"
allowed=["manifest.json","release-gates.json","logs/status-report.json","logs/cp1-real-cdp.json","logs/cp1-alias-restart.json","logs/cp4-recovery.json"]
with zipfile.ZipFile(out,"w",zipfile.ZIP_DEFLATED) as z:
 for rel in allowed:
  p=root/rel
  if p.exists():z.write(p,rel)
print("DEBUG_BUNDLE_PASS",out)
