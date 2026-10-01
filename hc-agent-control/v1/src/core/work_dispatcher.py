import time,json,pathlib
from src.adapters.uia_exact_transport import UIAExactTransport
PROMPTS={
 "HC AutoDebug":"Tiếp tục hỗ trợ debug theo checkpoint hiện tại. Diagnose trước, không redo PASS; fix tối thiểu, test targeted rồi full regression. Nếu không có lỗi mới thì báo PASS và chờ.",
 "HC Agent Control":"Tiếp tục giai đoạn hiện tại từ checkpoint gần nhất. Không redo phần PASS; xử lý TODO kế tiếp, tự test và cập nhật evidence.",
 "HC Insight Master V1":"Tiếp tục dự án từ checkpoint hiện tại. Không redo PASS; xử lý bước kế tiếp, test song song khi có thể và cập nhật checkpoint.",
 "HC Video Download":"Tiếp tục HC Video Downloader từ checkpoint hiện tại. Không redo PASS; xử lý TODO kế tiếp, test rồi cập nhật evidence.",
 "HC Design AI Studio":"Tiếp tục HC Design AI Studio từ checkpoint hiện tại. Không redo PASS; xử lý bước kế tiếp, test/demo rồi cập nhật checkpoint.",
}
class WorkDispatcher:
 def __init__(self,cooldown=600,ledger=None):
  self.cooldown=cooldown;self.ledger=pathlib.Path(ledger) if ledger else None;self.last={};self.locked=set();self.awaiting_ack=set();self.seen_busy=set();self._load()
 def _load(self):
  if not self.ledger or not self.ledger.exists():return
  try:
   d=json.loads(self.ledger.read_text(encoding="utf-8"));self.last={k:float(v) for k,v in d.get("last",{}).items()};self.locked=set(d.get("locked",[]));self.awaiting_ack=set(d.get("awaiting_ack",[]));self.seen_busy=set(d.get("seen_busy",[]))
  except Exception:pass
 def _save(self):
  if not self.ledger:return
  self.ledger.parent.mkdir(parents=True,exist_ok=True);tmp=self.ledger.with_suffix(".tmp");tmp.write_text(json.dumps({"last":self.last,"locked":sorted(self.locked),"awaiting_ack":sorted(self.awaiting_ack),"seen_busy":sorted(self.seen_busy)},indent=2),encoding="utf-8");tmp.replace(self.ledger)
 def observe(self,row):
  cid=row.get("conversation_id");state=row.get("stable_state") or row.get("state")
  if not cid or cid not in self.awaiting_ack:return
  if state in ("WORKING","WAITING"):self.seen_busy.add(cid);self._save()
  elif state=="READY_STABLE" and cid in self.seen_busy:self.awaiting_ack.discard(cid);self.seen_busy.discard(cid);self._save()
 def eligible(self,row,now=None):
  self.observe(row);now=now or time.time();cid=row.get("conversation_id")
  return bool(cid and row.get("stable_state")=="READY_STABLE" and row.get("decision")=="CONTINUE_EXISTING" and cid not in self.locked and cid not in self.awaiting_ack and now-self.last.get(cid,0)>=self.cooldown and row.get("title") in PROMPTS)
 def dispatch(self,window,row,now=None):
  now=now or time.time();cid=row["conversation_id"];r=UIAExactTransport().send_once(window,row["title"],cid,PROMPTS[row["title"]])
  if r.get("ok"):self.last[cid]=now;self.awaiting_ack.add(cid);self._save()
  elif r.get("side_effect_uncertain"):self.locked.add(cid);self._save()
  return r
