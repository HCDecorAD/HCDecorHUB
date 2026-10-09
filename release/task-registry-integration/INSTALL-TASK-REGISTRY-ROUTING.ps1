$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\TransportMesh'
$gateway='D:\HCDecorHUB\Gateway\hc-local-gateway.mjs'
$mcp=Join-Path $root 'mcp-server.mjs'
$expectedGateway='EC347751FFA690D72532D3CD586477DAD3E9CEB90238B8A1A1DAFB55E049FFC8'
$stamp=Get-Date -Format 'yyyyMMdd-HHmmss'
$backup=Join-Path $root ('task-registry-backup-'+$stamp)
New-Item -ItemType Directory -Path $backup -Force|Out-Null
$report=Join-Path $root 'TASK-REGISTRY-INSTALL-RESULT.json'
$result=[ordered]@{status='FAILED';time=(Get-Date).ToString('o');backup=$backup;gateway=$gateway;mcp=$mcp;error=$null;changed=$false}
try {
 if((Get-FileHash $gateway -Algorithm SHA256).Hash -ne $expectedGateway){throw 'GATEWAY_SHA_CHANGED_ABORT'}
 $g=[IO.File]::ReadAllText($gateway)
 $m=[IO.File]::ReadAllText($mcp)
 $needle='}else return reply(res,400,{ok:false,error:"EXEC_TASK_NOT_ALLOWED"})'
 if(!$g.Contains($needle)){throw 'GATEWAY_ANCHOR_MISSING'}
 $branch='}else if(task==="meshcentral_status"||task==="meshcentral_logs"){const script="D:/HCDecorHUB/TransportMesh/MESH-TASK-READONLY.ps1";if(!fs.existsSync(script))return reply(res,503,{ok:false,error:"TASK_HANDLER_MISSING"});job={schema:"transwarp-local/job-v1",job_id:id,correlation_id:id,approved:true,action:"powershell_file",cwd:"D:/HCDecorHUB",script,arguments:["-Task",task]}'
 $g=$g.Replace($needle,$branch+$needle)
 $toolAnchor='{name:"local_exec",description:'
 $invokeAnchor='if(n==="local_exec"){'
 if(!$m.Contains($toolAnchor) -or !$m.Contains($invokeAnchor)){throw 'MCP_ANCHOR_MISSING'}
 $tools='{name:"meshcentral_status",description:"Read MeshCentral service status",inputSchema:{type:"object",properties:{},additionalProperties:false},annotations:{readOnlyHint:true}},{name:"meshcentral_logs",description:"Read MeshCentral repair log index",inputSchema:{type:"object",properties:{},additionalProperties:false},annotations:{readOnlyHint:true}},'
 $m=$m.Replace($toolAnchor,$tools+$toolAnchor)
 $m=$m.Replace($invokeAnchor,'if(n==="meshcentral_status"||n==="meshcentral_logs")return call("/local/exec","POST",{task:n,cwd:"D:/HCDecorHUB"});'+$invokeAnchor)
 Copy-Item $gateway (Join-Path $backup 'hc-local-gateway.mjs')
 Copy-Item $mcp (Join-Path $backup 'mcp-server.mjs')
 $gTemp=Join-Path $backup 'gateway.new.mjs';$mTemp=Join-Path $backup 'mcp.new.mjs'
 [IO.File]::WriteAllText($gTemp,$g,[Text.UTF8Encoding]::new($false))
 [IO.File]::WriteAllText($mTemp,$m,[Text.UTF8Encoding]::new($false))
 & node --check $gTemp; if($LASTEXITCODE -ne 0){throw 'GATEWAY_SYNTAX_FAILED'}
 & node --check $mTemp; if($LASTEXITCODE -ne 0){throw 'MCP_SYNTAX_FAILED'}
 $result.changed=$true
 Copy-Item $gTemp $gateway -Force
 Copy-Item $mTemp $mcp -Force
 $result.status='STAGED_RESTART_REQUIRED'
}catch{
 $result.error=$_.Exception.Message
 if($result.changed){Copy-Item (Join-Path $backup 'hc-local-gateway.mjs') $gateway -Force;Copy-Item (Join-Path $backup 'mcp-server.mjs') $mcp -Force;$result.status='ROLLED_BACK'}
}
$result|ConvertTo-Json -Depth 5|Set-Content $report -Encoding UTF8
Write-Output ('TASK_REGISTRY_INSTALL='+$result.status)
if($result.status -ne 'STAGED_RESTART_REQUIRED'){exit 1}
