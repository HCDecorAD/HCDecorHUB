$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB'
$src='D:\HCDecorHUB\API KEY'
$dst=Join-Path $root 'TunnelClient'
$profile='hc-imaster-mesh'
$tunnel='tunnel_6ac39f3cd0e881918431ac23295ebbcb'
$mcp='http://127.0.0.1:8772/mcp'
New-Item -ItemType Directory -Force -Path $dst | Out-Null
$zip=Get-ChildItem $src -File | Where-Object { $_.Name -match 'tunnel.*windows.*amd64.*\.zip$|windows.*amd64.*\.zip$' } | Sort-Object LastWriteTime -Descending | Select-Object -First 1
if(-not $zip){throw 'TUNNEL_CLIENT_ZIP_NOT_FOUND'}
Expand-Archive -Path $zip.FullName -DestinationPath $dst -Force
$exe=Get-ChildItem $dst -Recurse -File | Where-Object {$_.Name -match '^tunnel-client(\.exe)?$'} | Select-Object -First 1
if(-not $exe){throw 'TUNNEL_CLIENT_EXE_NOT_FOUND'}
if(-not $env:CONTROL_PLANE_API_KEY){$env:CONTROL_PLANE_API_KEY=[Environment]::GetEnvironmentVariable('CONTROL_PLANE_API_KEY','User')}
if(-not $env:CONTROL_PLANE_API_KEY){throw 'CONTROL_PLANE_API_KEY_NOT_AVAILABLE'}
$health=Invoke-RestMethod 'http://127.0.0.1:8772/health'
if(-not $health.ok){throw 'MCP_8772_NOT_HEALTHY'}
& $exe.FullName init --sample hc-imaster-mesh --profile $profile --tunnel-id $tunnel --mcp-server-url $mcp
if($LASTEXITCODE -ne 0){throw "INIT_FAILED_$LASTEXITCODE"}
& $exe.FullName doctor --profile $profile --explain
if($LASTEXITCODE -ne 0){throw "DOCTOR_FAILED_$LASTEXITCODE"}
$runner=Join-Path $dst 'run-tunnel.cmd'
Set-Content -Path $runner -Encoding ASCII -Value ('@echo off'+[Environment]::NewLine+'set "CONTROL_PLANE_API_KEY=%CONTROL_PLANE_API_KEY%"'+[Environment]::NewLine+'"'+$exe.FullName+'" run --profile '+$profile)
$vbs=Join-Path $dst 'run-tunnel-hidden.vbs'
Set-Content -Path $vbs -Encoding ASCII -Value ('CreateObject("Wscript.Shell").Run """'+$runner+'""",0,False')
$startup=Join-Path $env:APPDATA 'Microsoft\Windows\Start Menu\Programs\Startup\HC-iMaster-Mesh-Tunnel.vbs'
Copy-Item $vbs $startup -Force
Start-Process wscript.exe -ArgumentList ('"'+$vbs+'"') -WindowStyle Hidden
Start-Sleep -Seconds 6
$proc=Get-CimInstance Win32_Process | Where-Object {$_.Name -match 'tunnel-client' -and $_.CommandLine -match 'hc-imaster-mesh'} | Select-Object -First 1
if(-not $proc){throw 'TUNNEL_PROCESS_NOT_RUNNING'}
Write-Output ('TUNNEL_SETUP_PASS profile='+$profile+' tunnel='+$tunnel+' mcp='+$mcp+' pid='+$proc.ProcessId+' autostart='+$startup)
