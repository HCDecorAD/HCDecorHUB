$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\TransportMesh'
$out=Join-Path $root 'MESH-ADMIN-PREFLIGHT-RESULT.json'
$report=[ordered]@{schema='imaster/mesh-admin-preflight/v1';timestamp=(Get-Date).ToString('o');status='CHECKING';mcp=$null;service=$null;source=$null;registry=$null;script=$null;gateway_source=$null;errors=@()}
try {
 $mcp=Join-Path $root 'mcp-server.mjs'
 $report.source=@{path=$mcp;sha256=(Get-FileHash -LiteralPath $mcp -Algorithm SHA256).Hash}
 $report.registry=Get-Content -LiteralPath (Join-Path $root 'mesh-admin-task-registry.PROPOSAL.json') -Raw | ConvertFrom-Json
 $repair=Join-Path $root 'mesh-admin-repair.ps1'
 $report.script=@{exists=(Test-Path -LiteralPath $repair);sha256=$(if(Test-Path $repair){(Get-FileHash $repair -Algorithm SHA256).Hash}else{$null})}
 $gateway='D:\HCDecorHUB\Gateway\hc-local-gateway.mjs'
 $report.gateway_source=@{exists=(Test-Path $gateway);sha256=$(if(Test-Path $gateway){(Get-FileHash $gateway -Algorithm SHA256).Hash}else{$null});content_not_exported=$true}
 $report.service=@(Get-CimInstance Win32_Service | Where-Object {$_.Name -eq 'meshcentral.exe'} | Select-Object Name,State,StartMode,PathName)
 $report.mcp=Invoke-RestMethod -Uri 'http://127.0.0.1:8772/health' -TimeoutSec 5
 if(!$report.mcp.ok -or !$report.script.exists -or !$report.gateway_source.exists){$report.status='INCOMPLETE'}else{$report.status='PASS_PREFLIGHT'}
} catch {$report.status='FAIL';$report.errors+=($_.Exception.Message)}
finally{$report.timestamp=(Get-Date).ToString('o');$report|ConvertTo-Json -Depth 12|Set-Content -LiteralPath $out -Encoding UTF8;Write-Output ('PREFLIGHT='+$report.status)}
if($report.status -ne 'PASS_PREFLIGHT'){exit 1}
