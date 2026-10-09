$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\TransportMesh'
$src=Join-Path $root 'mcp-server.mjs'
$out=Join-Path $root 'MCP-RESTART-RESULT.json'
$node='C:\Program Files\nodejs\node.exe'
$report=[ordered]@{schema='imaster/mcp-safe-restart/v1';timestamp=(Get-Date).ToString('o');status='STARTED';old_pid=$null;new_pid=$null;source_sha256=$null;health=$null;tools_count=0;selftest=$null;gateway_diagnostics=$null;error=$null}
function Invoke-Mcp([string]$name,[hashtable]$args) {
 $req=@{jsonrpc='2.0';id=7;method='tools/call';params=@{name=$name;arguments=$args}} | ConvertTo-Json -Depth 7 -Compress
 Invoke-RestMethod -Uri 'http://127.0.0.1:8772/mcp' -Method Post -ContentType 'application/json' -Body $req -TimeoutSec 15
}
try {
 if(!(Test-Path $node)){throw 'NODE_NOT_FOUND'}
 if(!(Test-Path $src)){throw 'MCP_SOURCE_NOT_FOUND'}
 & $node --check $src
 if($LASTEXITCODE -ne 0){throw 'SOURCE_SYNTAX_FAIL'}
 $report.source_sha256=(Get-FileHash $src -Algorithm SHA256).Hash
 $listeners=@(Get-NetTCPConnection -LocalPort 8772 -State Listen -ErrorAction SilentlyContinue)
 if($listeners.Count -gt 1){throw 'MULTIPLE_MCP_LISTENERS'}
 if($listeners.Count -eq 1){
  $pidOwner=[int]$listeners[0].OwningProcess
  $proc=Get-CimInstance Win32_Process -Filter "ProcessId=$pidOwner"
  if(!$proc -or $proc.Name -ne 'node.exe' -or $proc.CommandLine -notmatch '(^|[\\/\s])mcp-server\.mjs([\s"\x27]|$)'){throw 'PORT_OWNER_MISMATCH_ABORT'}
  $report.old_pid=$pidOwner
  Stop-Process -Id $pidOwner -ErrorAction Stop
  Start-Sleep -Seconds 2
  if(@(Get-NetTCPConnection -LocalPort 8772 -State Listen -ErrorAction SilentlyContinue).Count -ne 0){throw 'PORT_NOT_RELEASED'}
 }
 $new=Start-Process -FilePath $node -ArgumentList 'mcp-server.mjs' -WorkingDirectory $root -WindowStyle Hidden -PassThru
 $report.new_pid=$new.Id
 $ready=$false
 for($i=0;$i -lt 20;$i++){Start-Sleep -Milliseconds 600;try{$h=Invoke-RestMethod -Uri 'http://127.0.0.1:8772/health' -TimeoutSec 2;if($h.ok -and $h.id -eq 'HC_IMASTER_MCP'){$ready=$true;$report.health=$h;break}}catch{}}
 if(!$ready){throw 'HEALTH_TIMEOUT'}
 $owners=@(Get-NetTCPConnection -LocalPort 8772 -State Listen -ErrorAction Stop)
 if($owners.Count -ne 1 -or [int]$owners[0].OwningProcess -ne [int]$new.Id){throw 'LISTENER_PID_MISMATCH'}
 $listReq=@{jsonrpc='2.0';id=8;method='tools/list';params=@{}}|ConvertTo-Json -Depth 5 -Compress
 $list=Invoke-RestMethod -Uri 'http://127.0.0.1:8772/mcp' -Method Post -ContentType 'application/json' -Body $listReq -TimeoutSec 6
 $report.tools_count=@($list.result.tools).Count
 if($report.tools_count -lt 10){throw 'TOOLS_LIST_INCOMPLETE'}
 foreach($task in @('selftest','gateway-diagnostics')){
  try{$v=Invoke-Mcp 'local_exec' @{command=$task};$report[$task.Replace('-','_')]=$v.result}catch{$report[$task.Replace('-','_')]=@{error=$_.Exception.Message}}
 }
 $report.status='PASS'
} catch {$report.status='FAIL';$report.error=$_.Exception.Message}
finally{$report.timestamp=(Get-Date).ToString('o');$report|ConvertTo-Json -Depth 12|Set-Content -LiteralPath $out -Encoding UTF8;Write-Output ("MCP_RESTART_STATUS="+$report.status);Write-Output ("RESULT="+$out)}
if($report.status -ne 'PASS'){exit 1}
