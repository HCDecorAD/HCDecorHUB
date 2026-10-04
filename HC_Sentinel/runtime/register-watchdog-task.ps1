$ErrorActionPreference = "Stop"
$TaskName = "HC Sentinel Watchdog"
$ScriptPath = Join-Path $PSScriptRoot "watchdog-loop.ps1"
$TaskRun = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "' + $ScriptPath + '"'

& schtasks.exe /Create /F /TN $TaskName /TR $TaskRun /SC MINUTE /MO 2 | Out-Null
if ($LASTEXITCODE -ne 0) {
  throw "Failed to register HC Sentinel watchdog task"
}

Write-Host "HC Sentinel watchdog task registered"
