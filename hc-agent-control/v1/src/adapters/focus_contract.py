from .cdp_rpc import CDPRPC
class FocusContract:
 @staticmethod
 def focus(page):
  rpc=CDPRPC(page["websocket"])
  try:
   rpc.call("Page.bringToFront")
   rpc.call("Runtime.evaluate",{"expression":"document.querySelector('#prompt-textarea')?.focus()"})
   r=rpc.call("Runtime.evaluate",{"expression":"document.activeElement && document.activeElement.id === 'prompt-textarea'","returnByValue":True})
   if not bool(r.get("result",{}).get("value")):raise RuntimeError("composer focus verification failed")
   return True
  finally:rpc.close()
