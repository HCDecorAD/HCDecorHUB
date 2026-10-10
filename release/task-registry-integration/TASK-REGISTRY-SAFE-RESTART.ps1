$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\TransportMesh'
$gw='D:\HCDecorHUB\Gateway\hc-local-gateway.mjs'
$node='C:\Program Files\nodejs\node.exe'
$out=Join-Path $root 'TASK-REGISTRY-RESTART-RESULT.json'
$r=[ordered]@{status='FAIL';time=(Get-Date).ToString('o');gateway_pid_old=$null;gateway_pid_new=$null;mcp_restart=$null;gateway_health=$null;owner_lock=$null;error=$null}
try {
 if(!(Test-Path $node)){throw 'NODE_NOT_FOUND'}
 & $node --check $gw;if($LASTEXITCODE -ne 0){throw 'GATEWAY_SYNTAX_FAIL'}
 $p=@(Get-NetTCPConnection -LocalPort 8770 -State Listen -ErrorAction SilentlyContinue)
 if($p.Count -ne 1){throw 'GATEWAY_LISTENER_COUNT_UNEXPECTED'}
 $id=[int]$p[0].OwningProcess
 $proc=Get-CimInstance Win32_Process -Filter "ProcessId=$id"
 if(!$proc -or $proc.Name -ne 'node.exe' -or $proc.CommandLine -notmatch 'hc-local-gateway\.mjs'){throw 'GATEWAY_PORT_OWNER_MISMATCH'}
 $r.gateway_pid_old=$id
 Stop-Process -Id $id -ErrorAction Stop
 Start-Sleep -Seconds 2
 if(@(Get-NetTCPConnection -LocalPort 8770 -State Listen -ErrorAction SilentlyContinue).Count -ne 0){throw 'GATEWAY_PORT_NOT_RELEASED'}
 $n=Start-Process -FilePath $node -ArgumentList 'hc-local-gateway.mjs' -WorkingDirectory 'D:\HCDecorHUB\Gateway' -WindowStyle Hidden -PassThru
 $r.gateway_pid_new=$n.Id
 $ok=$false
 for($i=0;$i -lt 20;$i++){Start-Sleep -Milliseconds 600;try{$h=Invoke-RestMethod 'http://127.0.0.1:8770/health' -TimeoutSec 2;if($h.ok){$r.gateway_health=$h;$ok=$true;break}}catch{}}
 if(!$ok){throw 'GATEWAY_HEALTH_TIMEOUT'}
 $ms=Invoke-RestMethod 'http://127.0.0.1:8771/control/status' -TimeoutSec 8
 $r.owner_lock=$ms.data.zeus.ownerLock
 if($r.owner_lock -ne $true){throw 'OWNER_LOCK_NOT_CONFIRMED'}
 $restart=Join-Path $root 'MCP-SAFE-RESTART.ps1'
 if(!(Test-Path $restart)){throw 'MCP_RESTART_SCRIPT_MISSING'}
 & powershell.exe -NoProfile -File $restart
 if($LASTEXITCODE -ne 0){throw 'MCP_RESTART_FAILED'}
 $r.mcp_restart='PASS'
 $r.status='PASS'
}catch{$r.error=$_.Exception.Message}
finally{$r.time=(Get-Date).ToString('o');$r|ConvertTo-Json -Depth 7|Set-Content $out -Encoding UTF8;Write-Output ('TASK_REGISTRY_RESTART='+$r.status)}
if($r.status -ne 'PASS'){exit 1}
