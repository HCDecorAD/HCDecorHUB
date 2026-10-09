$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\TransportMesh'
$out=Join-Path $root 'MESH-REGISTRY-EXECUTOR-AUDIT.json'
$gw='D:\HCDecorHUB\Gateway\hc-local-gateway.mjs'
$mcp=Join-Path $root 'mcp-server.mjs'
$r=[ordered]@{schema='imaster/registry-executor-audit/v1';timestamp=(Get-Date).ToString('o');status='UNKNOWN';gateway_sha256=$null;mcp_sha256=$null;gateway_task_ids=@();mcp_task_ids=@();registry_task_ids=@();missing_in_gateway=@();missing_in_mcp=@();notes=@();error=$null}
try {
 $registry=Get-Content (Join-Path $root 'mesh-admin-task-registry.PROPOSAL.json') -Raw | ConvertFrom-Json
 $r.registry_task_ids=@($registry.tasks|ForEach-Object {$_.id})
 $r.gateway_sha256=(Get-FileHash $gw -Algorithm SHA256).Hash
 $r.mcp_sha256=(Get-FileHash $mcp -Algorithm SHA256).Hash
 $g=Get-Content $gw -Raw
 $m=Get-Content $mcp -Raw
 foreach($id in $r.registry_task_ids) {
  if($g.Contains($id)){$r.gateway_task_ids+= $id}else{$r.missing_in_gateway+= $id}
  if($m.Contains($id)){$r.mcp_task_ids+= $id}else{$r.missing_in_mcp+= $id}
 }
 $r.status=if($r.missing_in_gateway.Count -eq 0 -and $r.missing_in_mcp.Count -eq 0){'REFERENCES_FOUND_REVIEW_REQUIRED'}else{'NOT_ACTIVE_IN_EXECUTOR'}
 $r.notes+= 'Text reference scan only. Presence does not prove active routing, authorization, or runtime capability.'
 $r.notes+= 'No gateway source or secrets exported; no files or services modified.'
}catch{$r.status='ERROR';$r.error=$_.Exception.Message}
finally{$r|ConvertTo-Json -Depth 8|Set-Content -LiteralPath $out -Encoding UTF8;Write-Output ('REGISTRY_AUDIT='+$r.status)}
if($r.status -eq 'ERROR'){exit 1}
