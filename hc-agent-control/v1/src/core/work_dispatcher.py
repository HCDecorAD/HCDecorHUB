import time
from src.adapters.uia_exact_transport import UIAExactTransport
PROMPTS={
 "HC AutoDebug":"Tiếp tục hỗ trợ debug theo checkpoint hiện tại. Diagnose trước, không redo PASS; fix tối thiểu, test targeted rồi full regression. Nếu không có lỗi mới thì báo PASS và chờ.",
 "HC Agent Control":"Tiếp tục giai đoạn hiện tại từ checkpoint gần nhất. Không redo phần PASS; xử lý TODO kế tiếp, tự test và cập nhật evidence.",
 "HC Insight Master V1":"Tiếp tục dự án từ checkpoint hiện tại. Không redo PASS; xử lý bước kế tiếp, test song song khi có thể và cập nhật checkpoint.",
 "HC Video Download":"Tiếp tục HC Video Downloader từ checkpoint hiện tại. Không redo PASS; xử lý TODO kế tiếp, test rồi cập nhật evidence.",
 "HC Design AI Studio":"Tiếp tục HC Design AI Studio từ checkpoint hiện tại. Không redo PASS; xử lý bước kế tiếp, test/demo rồi cập nhật checkpoint.",
}
class WorkDispatcher:
 def __init__(self,cooldown=600):self.cooldown=cooldown;self.last={};self.locked=set()
 def eligible(self,row,now=None):
  now=now or time.time();cid=row.get("conversation_id")
  return bool(cid and row.get("stable_state")=="READY_STABLE" and row.get("decision")=="CONTINUE_EXISTING" and cid not in self.locked and now-self.last.get(cid,0)>=self.cooldown and row.get("title") in PROMPTS)
 def dispatch(self,window,row,now=None):
  now=now or time.time();cid=row["conversation_id"];r=UIAExactTransport().send_once(window,row["title"],cid,PROMPTS[row["title"]])
  if r.get("ok"):self.last[cid]=now
  elif r.get("side_effect_uncertain"):self.locked.add(cid)
  return r
