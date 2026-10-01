import json
class CDPReadOnlyContract:
 ALLOWED={"Runtime.enable","Runtime.evaluate"}
 FORBIDDEN_PREFIX=("Input.","Page.navigate","DOM.set","Runtime.callFunctionOn")
 @classmethod
 def validate(cls,method):
  if method in cls.ALLOWED:return True
  if method.startswith(cls.FORBIDDEN_PREFIX):raise PermissionError("read-only CDP blocks "+method)
  raise PermissionError("CDP method not allowlisted: "+method)
def activity_expression():
 return """(()=>({streaming:!!document.querySelector('[data-testid="stop-button"],button[aria-label*="Stop"]'),stop_button:!!document.querySelector('[data-testid="stop-button"]'),composer_disabled:!!document.querySelector('textarea[disabled],div[contenteditable="false"][data-testid*="composer"]'),conversation_ready:!!document.querySelector('main')}))()"""
