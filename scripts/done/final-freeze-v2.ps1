$ErrorActionPreference='Stop'
$root=Split-Path -Parent (Split-Path -Parent $PSScriptRoot); Set-Location $root
$plan=Get-Content 'config\hc-group-remaining-done-plan.json' -Raw|ConvertFrom-Json
$dir=Join-Path $root '.runtime\remaining-done\packages'; $bad=@()
foreach($p in @($plan.packages|Where-Object {$_.id -ne 'D06'})){
 $file=Join-Path $dir ($p.id+'.manifest.json')
 if(-not(Test-Path $file)){$bad+=$p.id;continue}
 try{$m=Get-Content $file -Raw|ConvertFrom-Json}catch{$bad+=$p.id;continue}
 if($m.state -ne 'DONE' -or $m.evidence_verified -ne $true -or -not $m.fingerprint -or -not $m.source_sha){$bad+=$p.id}
}
if($bad.Count){Write-Host "HC_DONE_D06_WAIT dependencies=$($bad -join ',')";exit 10}
Write-Host 'HC_DONE_D06_MANIFEST_INTEGRITY_PASS dependencies=5 regression_replay=0'
exit 0
