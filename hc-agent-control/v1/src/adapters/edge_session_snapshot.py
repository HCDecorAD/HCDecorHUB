import pathlib,re,shutil,tempfile
CID=re.compile(rb"https://chatgpt\.com/c/([0-9a-f-]{20,})",re.I)
def snapshot_cids(session_dir=None):
 d=pathlib.Path(session_dir or pathlib.Path.home()/"AppData/Local/Microsoft/Edge/User Data/Default/Sessions")
 files=sorted(d.glob("Session_*"),key=lambda p:p.stat().st_mtime,reverse=True)
 for src in files:
  if src.stat().st_size<=0:continue
  tmp=pathlib.Path(tempfile.gettempdir())/"hc-autochat-session.snapshot"
  try:shutil.copyfile(src,tmp);b=tmp.read_bytes()
  except (PermissionError,OSError):continue
  if not b.startswith(b"SNSS"):continue
  out=[]
  for m in CID.finditer(b):
   cid=m.group(1).decode("ascii").lower()
   if cid not in out:out.append(cid)
  return out
 return []
