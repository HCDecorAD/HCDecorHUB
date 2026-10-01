from .cdp_rpc import CDPRPC
class FocusContract:
 @staticmethod
 def focus(page):
  rpc=CDPRPC(page["websocket"])
  try:
   rpc.call("Page.bringToFront")
   sel="#prompt-textarea, div[role='textbox'][contenteditable='true']"
   rpc.call("Runtime.evaluate",{"expression":f"document.querySelector({sel!r})?.focus()"})
   r=rpc.call("Runtime.evaluate",{"expression":f"document.activeElement === document.querySelector({sel!r})","returnByValue":True})
   if not bool(r.get("result",{}).get("value")):raise RuntimeError("composer focus verification failed")
   return True
  finally:rpc.close()
