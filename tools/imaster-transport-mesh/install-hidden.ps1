param([string]$Root="D:\HCDecorHUB\TransportMesh",[string]$Repo="D:\HCDecorHUB\repos\HCDecorHUB")
$ErrorActionPreference="Stop"
New-Item -ItemType Directory -Force -Path $Root | Out-Null
Copy-Item (Join-Path $Repo "tools\imaster-transport-mesh\runtime.mjs") (Join-Path $Root "runtime.mjs") -Force
Copy-Item (Join-Path $Repo "tools\imaster-transport-mesh\router.mjs") (Join-Path $Root "router.mjs") -Force
Copy-Item (Join-Path $Repo "tools\imaster-transport-mesh\mcp-server.mjs") (Join-Path $Root "mcp-server.mjs") -Force
New-Item -ItemType Directory -Force -Path (Join-Path $Root "config") | Out-Null
Copy-Item (Join-Path $Repo "config\imaster-transport-mesh.json") (Join-Path $Root "config\imaster-transport-mesh.json") -Force

# Persist the mutation-path tokens into the hidden launchers. Startup processes do not reliably inherit
# variables from an already-running interactive shell, so resolve User/Machine values explicitly.
function Resolve-Env([string]$Name) {
  $v=[Environment]::GetEnvironmentVariable($Name,"Process")
  if(-not $v){$v=[Environment]::GetEnvironmentVariable($Name,"User")}
  if(-not $v){$v=[Environment]::GetEnvironmentVariable($Name,"Machine")}
  return $v
}
$meshToken=Resolve-Env "IMASTER_MESH_TOKEN"
if(-not $meshToken){$meshToken=Resolve-Env "HC_GATEWAY_TOKEN"}
if(-not $meshToken){throw "IMASTER_MESH_TOKEN_OR_HC_GATEWAY_TOKEN_REQUIRED"}

@"
@echo off
set HCDECOR_REPO=$Root
set IMASTER_MESH_CONFIG=$Root\config\imaster-transport-mesh.json
set IMASTER_MESH_STATE=$Root\state.json
set IMASTER_MESH_TOKEN=$meshToken
set HC_GATEWAY_TOKEN=$meshToken
cd /d "$Root"
node runtime.mjs >> mesh.log 2>&1
"@ | Set-Content -Encoding ASCII (Join-Path $Root "run.cmd")
@"
Set WshShell = CreateObject("WScript.Shell")
WshShell.Run """$Root\run.cmd""", 0, False
"@ | Set-Content -Encoding ASCII (Join-Path $Root "run-hidden.vbs")
$startup=[Environment]::GetFolderPath("Startup")
Copy-Item (Join-Path $Root "run-hidden.vbs") (Join-Path $startup "iMaster-Transport-Mesh.vbs") -Force
$old = Get-NetTCPConnection -LocalPort 8771 -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1
if($old){ taskkill /PID $old.OwningProcess /F | Out-Null }
Start-Sleep -Milliseconds 800
Start-Process wscript.exe -ArgumentList ('"'+(Join-Path $Root "run-hidden.vbs")+'"') -WindowStyle Hidden

@"
@echo off
cd /d "$Root"
node mcp-server.mjs >> mcp.log 2>&1
"@ | Set-Content -Encoding ASCII (Join-Path $Root "run-mcp.cmd")
@"
Set WshShell = CreateObject("WScript.Shell")
WshShell.Run """$Root\run-mcp.cmd""", 0, False
"@ | Set-Content -Encoding ASCII (Join-Path $Root "run-mcp-hidden.vbs")
Copy-Item (Join-Path $Root "run-mcp-hidden.vbs") (Join-Path $startup "iMaster-Mesh-MCP.vbs") -Force
$mcpOld = Get-NetTCPConnection -LocalPort 8772 -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1
if($mcpOld){ taskkill /PID $mcpOld.OwningProcess /F | Out-Null }
Start-Process wscript.exe -ArgumentList ('"'+(Join-Path $Root "run-mcp-hidden.vbs")+'"') -WindowStyle Hidden
Write-Output "IMASTER_TRANSPORT_MESH_INSTALLED $Root"
