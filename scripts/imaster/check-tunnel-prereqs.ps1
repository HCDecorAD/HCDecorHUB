$ErrorActionPreference="Stop"
$root="D:\HCDecorHUB\Tunnel"
New-Item -ItemType Directory -Force -Path $root | Out-Null
$tc=Get-Command tunnel-client -ErrorAction SilentlyContinue
$key=[bool]$env:CONTROL_PLANE_API_KEY
$tunnel=[bool]$env:IMASTER_TUNNEL_ID
$mcp=$false
try { $r=Invoke-RestMethod -Uri "http://127.0.0.1:8772/health" -TimeoutSec 3; $mcp=[bool]$r.ok } catch {}
[pscustomobject]@{tunnel_client_installed=[bool]$tc;control_plane_api_key_present=$key;tunnel_id_present=$tunnel;mcp_local_8772=$mcp} | ConvertTo-Json -Compress
if($tc){ & tunnel-client help quickstart | Select-Object -First 20 }
