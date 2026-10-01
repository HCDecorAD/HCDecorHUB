import pathlib,os
class AlreadyRunning(RuntimeError):pass
class SingleInstance:
 def __init__(self,path):self.path=pathlib.Path(path);self.owned=False
 def acquire(self):
  self.path.parent.mkdir(parents=True,exist_ok=True)
  try:
   fd=os.open(str(self.path),os.O_CREAT|os.O_EXCL|os.O_WRONLY);os.write(fd,str(os.getpid()).encode());os.close(fd);self.owned=True;return True
  except FileExistsError:raise AlreadyRunning(str(self.path))
 def release(self):
  if self.owned:
   try:self.path.unlink()
   except FileNotFoundError:pass
   self.owned=False
