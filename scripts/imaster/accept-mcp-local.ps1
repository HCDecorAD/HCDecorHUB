$ErrorActionPreference = "Stop"
$health = Invoke-RestMethod -Uri "http://127.0.0.1:8772/health"
$initBody = @{ jsonrpc="2.0"; id=1; method="initialize"; params=@{ protocolVersion="2025-06-18"; capabilities=@{}; clientInfo=@{name="hc-acceptance";version="1.0"} } } | ConvertTo-Json -Depth 8
$init = Invoke-RestMethod -Method Post -Uri "http://127.0.0.1:8772/mcp" -ContentType "application/json" -Body $initBody
$listBody = @{ jsonrpc="2.0"; id=2; method="tools/list"; params=@{} } | ConvertTo-Json -Depth 8
$list = Invoke-RestMethod -Method Post -Uri "http://127.0.0.1:8772/mcp" -ContentType "application/json" -Body $listBody
$statusBody = @{ jsonrpc="2.0"; id=3; method="tools/call"; params=@{name="mesh_status";arguments=@{}} } | ConvertTo-Json -Depth 8
$status = Invoke-RestMethod -Method Post -Uri "http://127.0.0.1:8772/mcp" -ContentType "application/json" -Body $statusBody
if (-not $health.ok) { throw "MCP_HEALTH_FAIL" }
if (($list.result.tools | Measure-Object).Count -lt 6) { throw "MCP_TOOLS_FAIL" }
Write-Output "MCP_LOCAL_ACCEPTANCE_PASS"
Write-Output ($health | ConvertTo-Json -Compress)
Write-Output ($status | ConvertTo-Json -Depth 10 -Compress)
