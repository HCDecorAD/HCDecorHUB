Set shell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")
base = fso.GetParentFolderName(WScript.ScriptFullName)
cmd = "cmd /c """ & base & "\start-sentinel.bat"""
tray = "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File """ & base & "\tray-agent.ps1"""
shell.Run cmd, 0, False
shell.Run tray, 0, False
