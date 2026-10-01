import ctypes,os
from ctypes import wintypes
u=ctypes.windll.user32
def edge_windows():
 out=[];EP=ctypes.WINFUNCTYPE(wintypes.BOOL,wintypes.HWND,wintypes.LPARAM)
 @EP
 def cb(h,l):
  if not u.IsWindowVisible(h):return True
  n=u.GetWindowTextLengthW(h)
  if n<1:return True
  b=ctypes.create_unicode_buffer(n+1);u.GetWindowTextW(h,b,n+1)
  pid=wintypes.DWORD();u.GetWindowThreadProcessId(h,ctypes.byref(pid))
  PROCESS_QUERY_LIMITED_INFORMATION=0x1000
  k=ctypes.windll.kernel32;ph=k.OpenProcess(PROCESS_QUERY_LIMITED_INFORMATION,False,pid.value)
  if not ph:return True
  try:
   size=wintypes.DWORD(32768);p=ctypes.create_unicode_buffer(size.value)
   if not k.QueryFullProcessImageNameW(ph,0,p,ctypes.byref(size)):return True
   if os.path.basename(p.value).lower()!="msedge.exe":return True
  finally:k.CloseHandle(ph)
  out.append({"hwnd":int(h),"pid":pid.value,"title":b.value})
  return True
 u.EnumWindows(cb,0);return out
