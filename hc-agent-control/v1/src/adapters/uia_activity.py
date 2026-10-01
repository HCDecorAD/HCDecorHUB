import uiautomation as auto
def _walk(c,d=0):
 if d>14:return
 for x in c.GetChildren():yield x;yield from _walk(x,d+1)
def selected_activity(window_name):
 """Read-only UIA facts for the currently selected browser tab."""
 root=auto.GetRootControl()
 w=next((x for x in root.GetChildren() if (x.Name or "")==window_name),None)
 if not w:return {}
 facts={"conversation_visible":False,"composer_enabled":None,"composer_disabled":False,"stop_button":False,"assistant_busy":False,"explicit_error":False,"retry_button":False,"blocked_banner":False}
 for c in _walk(w):
  name=(c.Name or "").strip().lower();typ=c.ControlTypeName
  if typ=="EditControl" and (c.ClassName or "")=="Textfield" and (c.AutomationId or "")!="view_1017":
   facts["conversation_visible"]=True
   try:enabled=bool(c.IsEnabled)
   except Exception:enabled=None
   facts["composer_enabled"]=enabled;facts["composer_disabled"]=enabled is False
  if typ=="ButtonControl":
   if name in ("stop generating","stop","dừng tạo","dừng"):facts["stop_button"]=True
   if "retry" in name or "thử lại" in name:facts["retry_button"]=True
  if any(x in name for x in ("something went wrong","đã xảy ra lỗi","network error","lỗi mạng")):facts["explicit_error"]=True
  if any(x in name for x in ("blocked","bị chặn")):facts["blocked_banner"]=True
 facts["assistant_busy"]=facts["stop_button"] or facts["composer_disabled"]
 return facts
