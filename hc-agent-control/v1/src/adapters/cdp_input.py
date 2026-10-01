from .cdp_rpc import CDPRPC
class CDPInput:
 def __init__(self,page):self.page=page
 def insert_text(self,text):
  rpc=CDPRPC(self.page["websocket"])
  try:rpc.call("Input.insertText",{"text":text});return {"ok":True}
  finally:rpc.close()
 def press_enter(self):
  rpc=CDPRPC(self.page["websocket"])
  try:
   rpc.call("Input.dispatchKeyEvent",{"type":"keyDown","key":"Enter","code":"Enter","windowsVirtualKeyCode":13,"nativeVirtualKeyCode":13})
   rpc.call("Input.dispatchKeyEvent",{"type":"keyUp","key":"Enter","code":"Enter","windowsVirtualKeyCode":13,"nativeVirtualKeyCode":13});return {"ok":True}
  finally:rpc.close()
 def submit(self):
  rpc=CDPRPC(self.page["websocket"])
  try:
   js=r"""(()=>{const e=document.querySelector("#prompt-textarea, div[role='textbox'][contenteditable='true']");if(!e)return false;const body=e.closest('[class*="ComposerLayoutBody"]');if(!body)return false;const bs=[...body.querySelectorAll('button')].filter(b=>!b.disabled&&(b.offsetWidth||b.offsetHeight));const b=bs[bs.length-1];if(!b)return false;b.click();return true})()"""
   r=rpc.call("Runtime.evaluate",{"expression":js,"returnByValue":True})
   if not bool(r.get("result",{}).get("value")):raise RuntimeError("composer submit button unavailable")
   return {"ok":True}
  finally:rpc.close()
 def type_and_enter(self,text):self.insert_text(text);return self.submit()
