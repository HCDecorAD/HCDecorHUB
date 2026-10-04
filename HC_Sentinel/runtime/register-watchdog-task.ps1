$ErrorActionPreference = "Stop"
$TaskName = "HC Sentinel Watchdog"
$ScriptPath = Join-Path $PSScriptRoot "watchdog-loop.ps1"
$Args = '-NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "' + $ScriptPath + '"'
$Action = New-ScheduledTaskAction -Execute "powershell.exe" -Argument $Args
$Trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1)
$Trigger.RepetitionInterval = (New-TimeSpan -Minutes 2)
$Trigger.RepetitionDuration = (New-TimeSpan -Days 3650)
$Settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew
Register-ScheduledTask -TaskName $TaskName -Action $Action -Trigger $Trigger -Settings $Settings -Force | Out-Null
Write-Host "HC Sentinel watchdog task registered"
