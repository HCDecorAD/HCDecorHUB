$ErrorActionPreference='Continue'
$root='D:\HCDecorHUB'
$repo=Join-Path $root 'repos\HCDecorHUB'
$runtime=Join-Path $root 'runtime\hcdr-supervisor'
New-Item -ItemType Directory -Force -Path $runtime|Out-Null
$workers=@(
 @{Name='prod';Launcher=Join-Path $repo 'tools\hcdr-relay\HCDR-Remote-Free.cmd';Heartbeat=Join-Path $root 'runtime\hcdr-relay-heartbeat.json';Match='relay-agent.mjs'},
 @{Name='v12';Launcher=Join-Path $repo 'tools\hcdr-relay\HCDR-V12-Test.cmd';Heartbeat=Join-Path $root 'HCDR Remote MCP\runtime\hcdr-v12-heartbeat.json';Match='relay-agent-v12.mjs'}
)
function Fresh($p){
 if(!(Test-Path $p)){return $false}
 try{$j=Get-Content $p -Raw|ConvertFrom-Json;$t=[DateTimeOffset]::Parse($j.at);return $j.ok -and (([DateTimeOffset]::UtcNow-$t).TotalSeconds -lt 90)}catch{return $false}
}
function Running($match){
 return @(Get-CimInstance Win32_Process -Filter "Name='node.exe'" -ErrorAction SilentlyContinue|?{$_.CommandLine -match [regex]::Escape($match)}).Count -gt 0
}
while($true){
 foreach($w in $workers){
  if(!(Running $w.Match) -or !(Fresh $w.Heartbeat)){
   Get-CimInstance Win32_Process -Filter "Name='node.exe'" -ErrorAction SilentlyContinue|?{$_.CommandLine -match [regex]::Escape($w.Match)}|%{Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue}
   Start-Sleep -Seconds 2
   if(Test-Path $w.Launcher){Start-Process cmd.exe -ArgumentList '/c',('"' + $w.Launcher + '"') -WindowStyle Hidden}
   Add-Content -Path (Join-Path $runtime 'supervisor.log') -Value ((Get-Date).ToString('o')+' restart '+$w.Name)
  }
 }
 @{ok=$true;pid=$PID;at=[DateTimeOffset]::UtcNow.ToString('o');workers=@($workers|%{@{name=$_.Name;running=(Running $_.Match);fresh=(Fresh $_.Heartbeat)}})}|ConvertTo-Json -Depth 5|Set-Content -Encoding UTF8 (Join-Path $runtime 'heartbeat.json')
 Start-Sleep -Seconds 30
}
