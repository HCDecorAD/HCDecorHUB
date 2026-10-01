import time,pyperclip,uiautomation as auto
def composer_roundtrip(control,text):
 old=pyperclip.paste()
 try:
  control.SetFocus();pyperclip.copy(text);auto.SendKeys("{Ctrl}v",waitTime=.03);time.sleep(.2)
  auto.SendKeys("{Ctrl}a",waitTime=.03);auto.SendKeys("{Ctrl}c",waitTime=.03);time.sleep(.15)
  return pyperclip.paste()
 finally:pyperclip.copy(old)
