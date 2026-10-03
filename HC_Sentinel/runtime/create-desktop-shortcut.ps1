$ErrorActionPreference = "Stop"
$Desktop = [Environment]::GetFolderPath("Desktop")
$ShortcutPath = Join-Path $Desktop "HC Sentinel.lnk"
$Shell = New-Object -ComObject WScript.Shell
$Shortcut = $Shell.CreateShortcut($ShortcutPath)
$Shortcut.TargetPath = "$env:WINDIR\explorer.exe"
$Shortcut.Arguments = "http://127.0.0.1:43110"
$Shortcut.Description = "Open HC Sentinel Command Center"
$Shortcut.Save()
Write-Host "HC Sentinel desktop shortcut created"
