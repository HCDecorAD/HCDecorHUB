$ErrorActionPreference='Stop'
$root=Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $root
$runtime=Join-Path $root '.runtime\remaining-done'
New-Item -ItemType Directory -Force -Path $runtime | Out-Null
function RunWave([int]$wave,[string[]]$launchers){
  Write-Host "HC_DONE_WAVE_START wave=$wave packages=$($launchers -join ',')"
  $jobs=@()
  foreach($launcher in $launchers){
    $log=Join-Path $runtime ($launcher+'.log')
    $p=Start-Process -FilePath 'cmd.exe' -ArgumentList @('/d','/s','/c',"call $launcher") -WorkingDirectory $root -RedirectStandardOutput $log -RedirectStandardError ($log+'.err') -NoNewWindow -PassThru
    $jobs += [pscustomobject]@{launcher=$launcher;process=$p;log=$log}
  }
  $failed=@()
  foreach($j in $jobs){$j.process.WaitForExit();if($j.process.ExitCode -ne 0){$failed+=$j}}
  if($failed.Count){
    $state=[ordered]@{wave=$wave;state='NOT_DONE';failed=@($failed|ForEach-Object{$_.launcher});logs=@($failed|ForEach-Object{$_.log});next_action='AutoDebug exact failure; if repository evidence insufficient, use approved plugin/web research; fix then rerun this wave.'}
    $state|ConvertTo-Json -Depth 6|Set-Content -Encoding UTF8 (Join-Path $runtime 'research-request.json')
    Write-Host "HC_DONE_WAVE_BLOCKED wave=$wave research=.runtime\remaining-done\research-request.json"
    exit 20
  }
  Write-Host "HC_DONE_WAVE_PASS wave=$wave"
}
RunWave 1 @('hc-done-01-evidence.bat','hc-done-02-hcdr-live.bat','hc-done-03-autodebug-live.bat')
RunWave 2 @('hc-done-04-control-runtime.bat','hc-done-05-media-runtime.bat')
RunWave 3 @('hc-done-06-final-freeze.bat')
@{schema='hc-group-runtime-finish/v1';state='DONE';completed_at=(Get-Date).ToUniversalTime().ToString('o')}|ConvertTo-Json|Set-Content -Encoding UTF8 (Join-Path $runtime 'summary.json')
Write-Host 'HC_GROUP_REMAINING_DONE_PASS packages=6 waves=3'
