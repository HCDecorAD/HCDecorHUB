$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\TransportMesh'
$out=Join-Path $root 'TASK-REGISTRY-DEPLOY-GATE.json'
$r=[ordered]@{schema='imaster/task-registry-deploy-gate/v1';time=(Get-Date).ToString('o');status='CHECKING';mcp_health=$null;service=$null;source_hash=$null;registry=$null;blocked=@();notes=@()}
try {
 $r.mcp_health=Invoke-RestMethod 'http://127.0.0.1:8772/health' -TimeoutSec 5
 $svc=Get-CimInstance Win32_Service -Filter "Name='meshcentral.exe'"
 $r.service=@{name=$svc.Name;state=$svc.State;start_mode=$svc.StartMode}
 $src=Join-Path $root 'mcp-server.mjs'
 $r.source_hash=(Get-FileHash $src -Algorithm SHA256).Hash
 $reg=Get-Content (Join-Path $root 'mesh-admin-task-registry.PROPOSAL.json') -Raw | ConvertFrom-Json
 $r.registry=@($reg.tasks | Select-Object id,type)
 if($reg.deployment -ne 'ACTIVE'){$r.blocked+= 'REGISTRY_NOT_ACTIVE'}
 $code=Get-Content $src -Raw
 foreach($t in $reg.tasks){if(!$code.Contains($t.id)){$r.blocked+=('MCP_ROUTE_MISSING:'+ $t.id)}}
 $r.notes+= 'Gate is read-only. No executor allowlist changes, no restart, no deployment.'
 $r.status=if($r.blocked.Count){'BLOCKED'}else{'REVIEW_REQUIRED'}
}catch{$r.status='ERROR';$r.blocked+=($_.Exception.Message)}
finally{$r|ConvertTo-Json -Depth 8|Set-Content $out -Encoding UTF8;Write-Output ('DEPLOY_GATE='+$r.status);Write-Output ('REPORT='+$out)}
