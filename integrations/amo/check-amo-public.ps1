$ErrorActionPreference = 'Stop'
$root = 'D:\HCDecorHUB\HC_Visual_Builder'
$out = Join-Path $root 'evidence'
New-Item -ItemType Directory -Force -Path $out | Out-Null
$tasks = @(
  @{ Name='site'; Code={ $r=Invoke-WebRequest -UseBasicParsing 'https://amonguyen.hcdecorhub.com/' -TimeoutSec 25; if($r.StatusCode -ne 200){throw 'SITE_NOT_200'}; 'SITE_HTTP_200' } },
  @{ Name='catalog'; Code={ $r=Invoke-WebRequest -UseBasicParsing 'https://hc-shop-engine.huycuongonline.workers.dev/api/catalog' -Headers @{'x-store-id'='store_amo'} -TimeoutSec 25; $j=$r.Content | ConvertFrom-Json; if($r.StatusCode -ne 200 -or $null -eq $j.items){throw 'CATALOG_INVALID'}; 'CATALOG_OK items='+$j.items.Count } }
)
$jobs = foreach($t in $tasks){ Start-Job -Name $t.Name -ScriptBlock $t.Code }
$failed = $false
foreach($job in $jobs){
  Wait-Job $job -Timeout 45 | Out-Null
  if($job.State -ne 'Completed'){ Stop-Job $job; $failed=$true }
  $result = Receive-Job $job 2>&1 | Out-String
  Set-Content -Path (Join-Path $out ('amo-'+$job.Name+'.log')) -Value $result
  if($job.State -ne 'Completed' -or $job.ChildJobs[0].JobStateInfo.State -ne 'Completed' -or $job.ChildJobs[0].Error.Count -gt 0){ $failed=$true }
  Write-Host ($job.Name+': '+$job.State+' '+$result.Trim())
  Remove-Job $job -Force
}
if($failed){ Write-Host 'PUBLIC READINESS FAILED'; exit 1 }
Write-Host 'SITE AND API PASS. PUBLIC NOT CERTIFIED UNTIL BINDING AND LIVE PUBLISH TEST PASS.'
exit 0
