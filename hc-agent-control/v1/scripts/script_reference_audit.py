import pathlib,re,ast
root=pathlib.Path(__file__).resolve().parents[1];missing=[]
for p in (root/"scripts").glob("*.bat"):
 s=p.read_text(encoding="utf-8",errors="replace")
 for pat in (r"(scripts\\[^\s\"&|()]+\.bat)",r"(scripts\\[^\s\"&|()]+\.py)"):
  for m in re.finditer(pat,s,re.I):
   rel=m.group(1).replace("\\","/")
   if not (root/rel).exists():missing.append((p.name,rel))
for p in (root/"scripts").glob("*.py"):
 try:ast.parse(p.read_text(encoding="utf-8"))
 except SyntaxError as e:missing.append((p.name,"SYNTAX:"+str(e)))
print("SCRIPT_REFERENCE_AUDIT",len(list((root/"scripts").glob("*.bat"))),"BAT")
if missing:
 for x in missing:print("FAIL",*x)
 raise SystemExit(320)
print("SCRIPT_REFERENCE_AUDIT_PASS")
