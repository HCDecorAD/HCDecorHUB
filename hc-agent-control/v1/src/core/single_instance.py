import pathlib,os
class AlreadyRunning(RuntimeError):pass
class SingleInstance:
 def __init__(self,path):self.path=pathlib.Path(path);self.owned=False
 def _pid_alive(self,pid):
  if pid<=0:return False
  try:
   import ctypes
   h=ctypes.windll.kernel32.OpenProcess(0x1000,False,pid)
   if h:ctypes.windll.kernel32.CloseHandle(h);return True
   return False
  except Exception:return True
 def acquire(self):
  self.path.parent.mkdir(parents=True,exist_ok=True)
  if self.path.exists():
   try:pid=int(self.path.read_text(encoding="utf-8").strip())
   except Exception:pid=-1
   if not self._pid_alive(pid):
    try:self.path.unlink()
    except OSError:pass
  try:
   fd=os.open(str(self.path),os.O_CREAT|os.O_EXCL|os.O_WRONLY);os.write(fd,str(os.getpid()).encode());os.close(fd);self.owned=True;return True
  except FileExistsError:raise AlreadyRunning(str(self.path))
 def release(self):
  if self.owned:
   try:self.path.unlink()
   except FileNotFoundError:pass
   self.owned=False
