$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
$Launcher = Join-Path $PSScriptRoot "start-hidden.vbs"
$Startup = [Environment]::GetFolderPath("Startup")
$ShortcutPath = Join-Path $Startup "HC Sentinel.lnk"

$Shell = New-Object -ComObject WScript.Shell
$Shortcut = $Shell.CreateShortcut($ShortcutPath)
$Shortcut.TargetPath = "$env:WINDIR\System32\wscript.exe"
$Shortcut.Arguments = '"' + $Launcher + '"'
$Shortcut.WorkingDirectory = $Root
$Shortcut.Description = "HC Sentinel background startup"
$Shortcut.Save()

Write-Host "HC Sentinel startup registered"
Write-Host $ShortcutPath
