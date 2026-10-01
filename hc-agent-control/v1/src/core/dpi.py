def enable_windows_dpi():
 try:
  import ctypes
  try:ctypes.windll.shcore.SetProcessDpiAwareness(1)
  except Exception:ctypes.windll.user32.SetProcessDPIAware()
  return True
 except Exception:return False
