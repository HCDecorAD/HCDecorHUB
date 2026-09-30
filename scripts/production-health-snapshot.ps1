$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB'
$out=Join-Path $root 'runtime\monitoring'
$history=Join-Path $out 'history'
New-Item -ItemType Directory -Force $history|Out-Null
$base='https://hcdecor-hub.huycuongonline.workers.dev'
$targets=@(
 @{name='agent';url=$base+'/hub/agents'},
 @{name='runtime';url=$base+'/api/runtime'},
 @{name='contract';url=$base+'/api/public/contract'},
 @{name='readiness';url=$base+'/api/master/readiness'},
 @{name='commerce';url=$base+'/api/commerce/status'},
 @{name='hcdecor';url='https://hcdecorhub.com'},
 @{name='gsc';url='https://gscsenior.hcdecorhub.com'},
 @{name='amo';url='https://amonguyen.hcdecorhub.com'},
 @{name='shop-engine';url='https://hc-shop-engine.huycuongonline.workers.dev/api/health'}
)
$rows=@()
foreach($t in $targets){
 $sw=[Diagnostics.Stopwatch]::StartNew()
 try{
  $r=Invoke-WebRequest -UseBasicParsing $t.url -TimeoutSec 20
  $sw.Stop()
  $acceptable=($r.StatusCode -eq 200)
  if($acceptable -and $t.name -eq 'contract'){
   $j=$r.Content|ConvertFrom-Json
   $acceptable=($j.contract_version -eq '1.1' -and $j.blockers.commerce_verified -and $j.blockers.custom_domain_verified -and -not $j.guarantees.production_write)
  }
  if($acceptable -and $t.name -eq 'commerce'){
   $j=$r.Content|ConvertFrom-Json
   $acceptable=($j.ready -and $j.catalog.production_authority -and $j.catalog.verified_commerce_data -and -not $j.production_write)
  }
  $rows+=@{name=$t.name;url=$t.url;ok=$acceptable;status=[int]$r.StatusCode;latency_ms=$sw.ElapsedMilliseconds}
 }catch{
  $sw.Stop()
  $status=0
  try{$status=[int]$_.Exception.Response.StatusCode}catch{}
  $rows+=@{name=$t.name;url=$t.url;ok=$false;status=$status;latency_ms=$sw.ElapsedMilliseconds;error=$_.Exception.Message}
 }
}
$now=(Get-Date).ToUniversalTime()
$allOk=($rows.ok -notcontains $false)
$doc=@{checked_at=$now.ToString('o');all_ok=$allOk;healthy=@($rows|Where-Object ok).Count;total=$rows.Count;platform='cloudflare';targets=$rows}
$json=$doc|ConvertTo-Json -Depth 6
$json|Set-Content (Join-Path $out 'LATEST.json') -Encoding utf8
$json|Set-Content (Join-Path $history ($now.ToString('yyyyMMdd-HHmmss')+'.json')) -Encoding utf8
$recent=Get-ChildItem $history -Filter '*.json'|Sort-Object LastWriteTime -Descending|Select-Object -First 2
$confirmed=$false
if($recent.Count -ge 2){$confirmed=(@($recent|ForEach-Object{try{(Get-Content $_.FullName -Raw|ConvertFrom-Json).all_ok}catch{$true}}|Where-Object{$_ -eq $false}).Count -ge 2)}
$state=@{state=if($confirmed){'confirmed-degradation'}elseif($allOk){'healthy'}else{'suspected-degradation'};confirmed_degradation=$confirmed;consecutive_required=2;checked_at=$now.ToString('o');failed=@($rows|Where-Object{-not $_.ok}|ForEach-Object{$_.name})}
$state|ConvertTo-Json -Depth 4|Set-Content (Join-Path $out 'ALERT_STATE.json') -Encoding utf8
Get-ChildItem $history -Filter '*.json'|Where-Object LastWriteTime -lt (Get-Date).AddDays(-14)|Remove-Item -Force
@{snapshot=$doc;alert=$state}|ConvertTo-Json -Depth 7
