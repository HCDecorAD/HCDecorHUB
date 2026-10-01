$ErrorActionPreference='Stop'
$repo='D:\HCDecorHUB\repos\HCDecorHUB'
$runtime='D:\HCDecorHUB\runtime'
$launcher=Join-Path $repo 'tools\hcdr-relay\HCDR-Remote-Free.cmd'
New-Item -ItemType Directory -Force $runtime|Out-Null
Push-Location $repo
try {
 git fetch origin
 if($LASTEXITCODE){throw 'git fetch failed'}
 git status --porcelain
} finally {Pop-Location}
Get-CimInstance Win32_Process -Filter "Name='node.exe'" | Where-Object {$_.CommandLine -match 'hcdr-relay'} | ForEach-Object {Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue}
Remove-Item 'D:\HCDecorHUB\runtime\hcdr-relay.lock' -Force -ErrorAction SilentlyContinue
Start-Process cmd.exe -ArgumentList '/c',('"' + $launcher + '"') -WindowStyle Hidden
Start-Sleep 12
$hb='D:\HCDecorHUB\runtime\hcdr-relay-heartbeat.json'
if(!(Test-Path $hb)){throw 'HCDR heartbeat missing'}
$j=Get-Content $hb -Raw|ConvertFrom-Json
if(-not $j.ok){throw 'HCDR heartbeat not OK'}
$task='HCDecor-HCDR-Watchdog'
$watch='powershell.exe -NoProfile -ExecutionPolicy Bypass -File "'+(Join-Path $repo 'tools\hcdr-relay\watchdog.ps1')+'"'
schtasks /Create /F /SC MINUTE /MO 2 /TN $task /TR $watch | Out-Null
Write-Host ('HCDR_REPAIR_PASS pid='+$j.pid)
