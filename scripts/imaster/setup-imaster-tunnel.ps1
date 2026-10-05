$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB'
$dst=Join-Path $root 'TunnelClient'
$profile='hc-imaster-mesh'
$tunnel='tunnel_6ac39f3cd0e881918431ac23295ebbcb'
$mcp='http://127.0.0.1:8772/mcp'
$release='v0.0.15'
$zip=Join-Path $dst ('tunnel-client-'+$release+'-windows-amd64.zip')
$url='https://github.com/openai/tunnel-client/releases/download/'+$release+'/tunnel-client-'+$release+'-windows-amd64.zip'
New-Item -ItemType Directory -Force -Path $dst | Out-Null
$exe=Get-ChildItem $dst -Recurse -File -ErrorAction SilentlyContinue | Where-Object {$_.Name -eq 'tunnel-client.exe'} | Select-Object -First 1
if(-not $exe){
  Invoke-WebRequest -UseBasicParsing -Uri $url -OutFile $zip
  Expand-Archive -Path $zip -DestinationPath $dst -Force
  $exe=Get-ChildItem $dst -Recurse -File | Where-Object {$_.Name -eq 'tunnel-client.exe'} | Select-Object -First 1
}
if(-not $exe){throw 'FULL_TUNNEL_CLIENT_EXE_NOT_FOUND'}
& $exe.FullName --version
if(-not $env:CONTROL_PLANE_API_KEY){$env:CONTROL_PLANE_API_KEY=[Environment]::GetEnvironmentVariable('CONTROL_PLANE_API_KEY','User')}
if(-not $env:CONTROL_PLANE_API_KEY){
  $keyFile='D:\HCDecorHUB\API KEY\API KEY OPEN AI.txt'
  if(Test-Path $keyFile){
    $candidate=(Get-Content -Raw $keyFile).Trim()
    if($candidate){$env:CONTROL_PLANE_API_KEY=$candidate}
  }
}
if(-not $env:CONTROL_PLANE_API_KEY){throw 'CONTROL_PLANE_API_KEY_NOT_AVAILABLE'}
$health=Invoke-RestMethod 'http://127.0.0.1:8772/health'
if(-not $health.ok){throw 'MCP_8772_NOT_HEALTHY'}
& $exe.FullName init --sample hc-imaster-mesh --profile $profile --tunnel-id $tunnel --mcp-server-url $mcp
if($LASTEXITCODE -ne 0){throw "INIT_FAILED_$LASTEXITCODE"}
& $exe.FullName doctor --profile $profile --explain
if($LASTEXITCODE -ne 0){throw "DOCTOR_FAILED_$LASTEXITCODE"}
$runner=Join-Path $dst 'run-tunnel.ps1'
$runnerText=@'
$ErrorActionPreference='Stop'
if(-not $env:CONTROL_PLANE_API_KEY){$env:CONTROL_PLANE_API_KEY=[Environment]::GetEnvironmentVariable('CONTROL_PLANE_API_KEY','User')}
& '__EXE__' run --profile hc-imaster-mesh
'@.Replace('__EXE__',$exe.FullName)
Set-Content -Path $runner -Encoding UTF8 -Value $runnerText
$vbs=Join-Path $dst 'run-tunnel-hidden.vbs'
$vbsText='CreateObject("Wscript.Shell").Run "powershell.exe -NoProfile -ExecutionPolicy Bypass -File ""'+$runner+'""",0,False'
Set-Content -Path $vbs -Encoding ASCII -Value $vbsText
$startup=Join-Path $env:APPDATA 'Microsoft\Windows\Start Menu\Programs\Startup\HC-iMaster-Mesh-Tunnel.vbs'
Copy-Item $vbs $startup -Force
Start-Process wscript.exe -ArgumentList ('"'+$vbs+'"') -WindowStyle Hidden
Start-Sleep -Seconds 8
$proc=Get-CimInstance Win32_Process | Where-Object {$_.Name -eq 'tunnel-client.exe' -and $_.CommandLine -match 'hc-imaster-mesh'} | Select-Object -First 1
if(-not $proc){throw 'TUNNEL_PROCESS_NOT_RUNNING'}
Write-Output ('TUNNEL_SETUP_PASS profile='+$profile+' tunnel='+$tunnel+' mcp='+$mcp+' pid='+$proc.ProcessId+' autostart=PASS')
