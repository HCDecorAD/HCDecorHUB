import sys,json,pathlib
def selftest():
 root=pathlib.Path(__file__).resolve().parents[2]
 checks={"python":sys.version.split()[0],"src":(root/"src").exists(),"send_armed":False}
 try:
  import tkinter,src.core.queue,src.core.registry,src.core.settings
  checks["imports"]=True
 except Exception as e:checks["imports"]=False;checks["error"]=str(e)
 ok=all(checks.get(k) is True for k in ("imports",))
 print(json.dumps({"schema":"hc-agent-control-exe-selftest/v1","ok":ok,"checks":checks}))
 return 0 if ok else 1
def main():
 if "--selftest" in sys.argv:return selftest()
 from src.ui.app import AutoChatApp
 AutoChatApp().mainloop();return 0
if __name__=="__main__":raise SystemExit(main())
