import json,re,time,pathlib,uiautomation as a
CID=re.compile(r"https?://chatgpt\.com/(?:g/[^/]+/)?c/([0-9a-f-]{20,})",re.I)
ALLOW=("ChatGPT HCDecor V9","HC AutoDebug","HC Agent Control","HC AutoChat","HC Video Download","HC Video Downloader","HC Insight","HC Design AI Studio")
def walk(x,d=0):
 if d>12:return
 for y in x.GetChildren():yield y;yield from walk(y,d+1)
def clean(s):return re.split(r"\s+-\s+(?:Đang ngủ|Sleeping|Mức sử dụng bộ nhớ|Memory usage)",s or "",maxsplit=1)[0].strip()
def addr(w):
 for c in walk(w):
  if c.ControlTypeName=="EditControl" and (c.AutomationId or "") in ("view_1017","view_1012"):
   try:return c.GetValuePattern().Value
   except:pass
def run():
 wins=[w for w in a.GetRootControl().GetChildren() if "Microsoft" in (w.Name or "") and "Edge" in (w.Name or "") and "about:blank" not in (w.Name or "")]
 if not wins:return {"status":"NO_EDGE"}
 w=wins[0];tabs=[c for c in walk(w) if c.ControlTypeName=="TabItemControl"]
 orig=next((t for t in tabs if t.GetSelectionItemPattern().IsSelected),None);rows=[]
 try:
  for t in tabs:
   title=clean(t.Name)
   if not any(x.lower() in title.lower() for x in ALLOW):continue
   t.GetSelectionItemPattern().Select();url=None;m=None;stable=0;last=None
   for _ in range(12):
    time.sleep(.25);now=next((x for x in tabs if x.GetSelectionItemPattern().IsSelected),None);nowtitle=clean(now.Name if now else "");candidate=addr(w);cm=CID.search(candidate or "")
    sig=(nowtitle,candidate)
    stable=stable+1 if sig==last else 1;last=sig
    if nowtitle==title and cm and stable>=2:url=candidate;m=cm;break
   ok=bool(m)
   rows.append({"title":title,"url":url if ok else None,"conversation_id":m.group(1).lower() if ok else None,"confidence":"EXACT_PEEK_SAME_TITLE" if ok else "REJECTED"})
 finally:
  if orig:orig.GetSelectionItemPattern().Select()
 out={"status":"OK","original_restored":clean(orig.Name) if orig else None,"entries":rows}
 p=pathlib.Path(__file__).resolve().parents[1]/"runtime"/"tab-identity-registry.json";p.parent.mkdir(exist_ok=True);p.write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding="utf-8")
 return out
if __name__=="__main__":print(json.dumps(run(),ensure_ascii=False,indent=2))
