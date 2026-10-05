param([string]$Root="D:\HCDecorHUB\TransportMesh",[string]$Repo="D:\HCDecorHUB\repos\HCDecorHUB")
$ErrorActionPreference="Stop"
New-Item -ItemType Directory -Force -Path $Root | Out-Null
Copy-Item (Join-Path $Repo "tools\imaster-transport-mesh\runtime.mjs") (Join-Path $Root "runtime.mjs") -Force
Copy-Item (Join-Path $Repo "tools\imaster-transport-mesh\router.mjs") (Join-Path $Root "router.mjs") -Force
New-Item -ItemType Directory -Force -Path (Join-Path $Root "config") | Out-Null
Copy-Item (Join-Path $Repo "config\imaster-transport-mesh.json") (Join-Path $Root "config\imaster-transport-mesh.json") -Force
@"
@echo off
set HCDECOR_REPO=$Root
set IMASTER_MESH_CONFIG=$Root\config\imaster-transport-mesh.json
set IMASTER_MESH_STATE=$Root\state.json
cd /d "$Root"
node runtime.mjs >> mesh.log 2>&1
"@ | Set-Content -Encoding ASCII (Join-Path $Root "run.cmd")
@"
Set WshShell = CreateObject("WScript.Shell")
WshShell.Run """$Root\run.cmd""", 0, False
"@ | Set-Content -Encoding ASCII (Join-Path $Root "run-hidden.vbs")
$startup=[Environment]::GetFolderPath("Startup")
Copy-Item (Join-Path $Root "run-hidden.vbs") (Join-Path $startup "iMaster-Transport-Mesh.vbs") -Force
Start-Process wscript.exe -ArgumentList ('"'+(Join-Path $Root "run-hidden.vbs")+'"') -WindowStyle Hidden
Write-Output "IMASTER_TRANSPORT_MESH_INSTALLED $Root"

@"
@echo off
cd /d "$Root"
start "" /b node worker.mjs >> worker.log 2>&1
node carrier-pump.mjs >> carrier.log 2>&1
"@ | Set-Content -Encoding ASCII (Join-Path $Root "run-carrier.cmd")
@"
Set WshShell = CreateObject("WScript.Shell")
WshShell.Run """$Root\run-carrier.cmd""", 0, False
"@ | Set-Content -Encoding ASCII (Join-Path $Root "run-carrier-hidden.vbs")
Copy-Item (Join-Path $Root "run-carrier-hidden.vbs") (Join-Path $startup "iMaster-Transport-Carrier.vbs") -Force
if ($env:IMASTER_CARRIER_GITHUB_REPO -and ($env:IMASTER_CARRIER_GITHUB_TOKEN -or $env:GITHUB_TOKEN)) {
 Start-Process wscript.exe -ArgumentList ('"'+(Join-Path $Root "run-carrier-hidden.vbs")+'"') -WindowStyle Hidden
}
