from .cdp_rpc import CDPRPC
class CDPInput:
 def __init__(self,page):self.page=page
 def type_and_enter(self,text):
  rpc=CDPRPC(self.page["websocket"])
  try:
   rpc.call("Input.insertText",{"text":text})
   rpc.call("Input.dispatchKeyEvent",{"type":"keyDown","key":"Enter","code":"Enter","windowsVirtualKeyCode":13,"nativeVirtualKeyCode":13})
   rpc.call("Input.dispatchKeyEvent",{"type":"keyUp","key":"Enter","code":"Enter","windowsVirtualKeyCode":13,"nativeVirtualKeyCode":13})
   return {"ok":True}
  finally:rpc.close()
