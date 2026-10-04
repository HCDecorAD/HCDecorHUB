$ErrorActionPreference = "Stop"
$Base = "http://127.0.0.1:43110"
$HiddenLauncher = Join-Path $PSScriptRoot "start-hidden.vbs"

try {
  $Response = Invoke-WebRequest -UseBasicParsing -Uri "$Base/api/status" -TimeoutSec 3
  if ($Response.StatusCode -eq 200) { exit 0 }
} catch {
}

Start-Process "$env:WINDIR\System32\wscript.exe" -ArgumentList ('"' + $HiddenLauncher + '"') -WindowStyle Hidden
exit 0
