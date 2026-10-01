import pathlib
BROWSERS={"edge":pathlib.Path.home()/"AppData/Local/Microsoft/Edge/User Data","chrome":pathlib.Path.home()/"AppData/Local/Google/Chrome/User Data"}
def chromium_profiles():
 out=[]
 for browser,base in BROWSERS.items():
  if not base.exists():continue
  for p in base.iterdir():
   s=p/"Sessions"
   if p.is_dir() and s.is_dir():
    out.append({"browser":browser,"profile":p.name,"sessions":str(s)})
 return out
