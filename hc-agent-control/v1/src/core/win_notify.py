def notify(title,message):
 # Dependency-free Windows notification fallback: taskbar beep + console-safe result.
 try:
  import ctypes
  ctypes.windll.user32.MessageBeep(0x00000040)
  return True
 except Exception:return False
