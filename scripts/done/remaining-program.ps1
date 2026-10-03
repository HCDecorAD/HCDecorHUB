$ErrorActionPreference='Stop'
$root=Split-Path -Parent (Split-Path -Parent $PSScriptRoot); Set-Location $root
$runtime=Join-Path $root '.runtime\remaining-done'; $pkgDir=Join-Path $runtime 'packages'
New-Item -ItemType Directory -Force -Path $pkgDir | Out-Null
$plan=Get-Content 'config\hc-group-remaining-done-plan.json' -Raw | ConvertFrom-Json

function Fingerprint($p){
  $head=(git rev-parse HEAD).Trim()
  return "$($p.id):$head:$($p.launcher)"
}
function ManifestPath($id){ Join-Path $pkgDir ($id+'.manifest.json') }
function Reusable($p){
  $m=ManifestPath $p.id
  if(-not(Test-Path $m)){return $false}
  try{$x=Get-Content $m -Raw|ConvertFrom-Json; return ($x.state -eq 'DONE' -and $x.fingerprint -eq (Fingerprint $p) -and $x.evidence_verified -eq $true)}catch{return $false}
}
function StartPackage($p){
  if(Reusable $p){Write-Host "HC_DONE_REUSE_PASS package=$($p.id)";return $null}
  $log=Join-Path $runtime ($p.id+'.log')
  $proc=Start-Process -FilePath 'cmd.exe' -ArgumentList @('/d','/s','/c',"call $($p.launcher)") -WorkingDirectory $root -RedirectStandardOutput $log -RedirectStandardError ($log+'.err') -NoNewWindow -PassThru
  return [pscustomobject]@{package=$p;process=$proc;log=$log}
}
function Seal($job){
  $job.process.WaitForExit()
  $p=$job.package; $ok=$job.process.ExitCode -eq 0
  $m=[ordered]@{schema='hc-done-package/v2';package_id=$p.id;state=if($ok){'DONE'}else{'FAILED'};fingerprint=(Fingerprint $p);source_sha=(git rev-parse HEAD).Trim();evidence_verified=$ok;exit_code=$job.process.ExitCode;log=$job.log;sealed_at=(Get-Date).ToUniversalTime().ToString('o')}
  $m|ConvertTo-Json -Depth 6|Set-Content -Encoding UTF8 (ManifestPath $p.id)
  return $m
}

$pending=@($plan.packages)
$failed=@()
while($pending.Count){
  $ready=@($pending|Where-Object{
    $p=$_
    @($p.depends_on|Where-Object{
      $dep=$_
      -not(Reusable ($plan.packages|Where-Object id -eq $dep|Select-Object -First 1))
    }).Count -eq 0
  })
  if(-not $ready.Count){
    $waiting=@($pending|ForEach-Object{$_.id})
    @{schema='hc-group-runtime-finish/v2';state='WAITING_EXTERNAL';waiting=$waiting;checkpointed=$true}|ConvertTo-Json|Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json')
    Write-Host "HC_DONE_YIELD waiting=$($waiting -join ',')"; exit 10
  }
  $jobs=@(); foreach($p in $ready){$j=StartPackage $p;if($j){$jobs+=$j}}
  foreach($j in $jobs){$m=Seal $j;if($m.state -ne 'DONE'){$failed+=$m}}
  $pending=@($pending|Where-Object{$ready.id -notcontains $_.id})
  if($failed.Count){
    @{schema='hc-group-runtime-finish/v2';state='RECOVERY_REQUIRED';failed=$failed;checkpointed=$true;next_action='AutoDebug failed packages only; preserve reusable PASS manifests.'}|ConvertTo-Json -Depth 8|Set-Content -Encoding UTF8 (Join-Path $runtime 'research-request.json')
    exit 20
  }
}
@{schema='hc-group-runtime-finish/v2';state='DONE';manifest_dir=$pkgDir;completed_at=(Get-Date).ToUniversalTime().ToString('o')}|ConvertTo-Json|Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json')
Write-Host 'HC_GROUP_REMAINING_DONE_PASS_V2 manifests=sealed reuse=enabled scheduler=dependency_ready'
