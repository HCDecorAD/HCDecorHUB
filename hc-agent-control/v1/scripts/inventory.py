import pathlib,re
root=pathlib.Path(__file__).resolve().parents[1];bats=sorted((root/"scripts").glob("*.bat"));missing=[]
for p in bats:
 text=p.read_text(encoding="utf-8",errors="replace")
 for m in re.finditer(r"(?:call\s+)?(scripts\\[^\s\"&|()]+\.bat)",text,re.I):
  rel=m.group(1).replace("\\","/")
  if not (root/rel).exists():missing.append((p.name,rel))
print("BAT_COUNT",len(bats))
if missing:
 print("BAT_REFERENCE_FAIL")
 for x in missing:print(*x)
 raise SystemExit(101)
print("BAT_REFERENCE_PASS")
