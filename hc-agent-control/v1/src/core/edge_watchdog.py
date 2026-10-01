import pathlib,subprocess,time,urllib.request,json
ROOT=pathlib.Path(__file__).resolve().parents[2]; PROFILE=ROOT/"runtime"/"edge-profile"; START=ROOT/"scripts"/"02_START_MANAGED_EDGE.bat"
def healthy():
 try:
  with urllib.request.urlopen("http://127.0.0.1:9222/json/version",timeout=2) as r:d=json.load(r)
  return bool(d.get("webSocketDebuggerUrl"))
 except Exception:return False
def ensure():
 if healthy():return True
 subprocess.run(["cmd","/c",str(START)],cwd=str(ROOT),timeout=35,check=False)
 for _ in range(10):
  if healthy():return True
  time.sleep(1)
 return False
def main():
 out=ROOT/"runtime"/"edge-watchdog.json";out.parent.mkdir(parents=True,exist_ok=True)
 while True:
  ok=ensure();out.write_text(json.dumps({"updated":time.time(),"cdp":"ONLINE" if ok else "OFFLINE","devtools_active_port":(PROFILE/"DevToolsActivePort").exists(),"policy":"NO_CHAT_NAVIGATION"},indent=2),encoding="utf-8");time.sleep(15)
if __name__=="__main__":main()
