FREE_KIT={
 "pywinauto":{"cost":0,"role":"Windows UIA/Win32 automation","mode":"CANARY"},
 "uiautomation":{"cost":0,"role":"UI Automation tree/events","mode":"CANARY"},
 "playwright":{"cost":0,"role":"browser locators/actionability","mode":"CANARY"},
 "opencv":{"cost":0,"role":"local visual matching/diff","mode":"FALLBACK"},
 "tesseract":{"cost":0,"role":"local OCR","mode":"LAST_RESORT"},
 "psutil":{"cost":0,"role":"process/session health","mode":"UTILITY"},
 "watchdog":{"cost":0,"role":"filesystem event observer","mode":"UTILITY"},
}
ORDER=("uiautomation","pywinauto","playwright","psutil","watchdog","opencv","tesseract")
def free_sensor_kit():
 return [{"name":n,**FREE_KIT[n]} for n in ORDER]
