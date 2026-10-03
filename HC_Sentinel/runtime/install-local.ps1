$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
$Runtime = Join-Path $Root "runtime"
$Launcher = Join-Path $Runtime "start-hidden.vbs"

if (-not (Get-Command node -ErrorAction SilentlyContinue)) {
  throw "Node.js is required"
}

$NodeMajor = [int]((node -p "process.versions.node.split('.')[0]"))
if ($NodeMajor -lt 22) {
  throw "Node.js 22 or newer is required"
}

$Folders = @(
  (Join-Path $Root "runtime-state"),
  (Join-Path $Root "evidence\live"),
  (Join-Path $Root "data\live"),
  (Join-Path $Root "logs")
)

foreach ($Folder in $Folders) {
  New-Item -ItemType Directory -Force -Path $Folder | Out-Null
}

Write-Host "HC Sentinel local install ready"
Write-Host "Launcher: $Launcher"
