$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB'
$out=Join-Path $root 'runtime\monitoring'
$history=Join-Path $out 'history'
New-Item -ItemType Directory -Force $history | Out-Null
$targets=@(
 @{name='agent';url='https://hcdecorhub.vercel.app/hub/agents'},
 @{name='runtime';url='https://hcdecorhub.vercel.app/api/runtime'},
 @{name='readiness';url='https://hcdecorhub.vercel.app/api/master/readiness'},
 @{name='commerce';url='https://hcdecorhub.vercel.app/api/commerce/status'},
 @{name='hcdecor';url='https://hcdecorhub.com'},
 @{name='gsc';url='https://gscsenior.vercel.app'},
 @{name='amo';url='https://amonguyen.vercel.app'},
 @{name='shop-engine';url='https://hc-shop-engine.huycuongonline.workers.dev/api/health'}
)
$rows=@(); foreach($t in $targets){$sw=[Diagnostics.Stopwatch]::StartNew();try{$r=Invoke-WebRequest -UseBasicParsing $t.url -TimeoutSec 20;$sw.Stop();$rows+=@{name=$t.name;url=$t.url;ok=($r.StatusCode -eq 200);status=[int]$r.StatusCode;bytes=$r.Content.Length;latency_ms=$sw.ElapsedMilliseconds}}catch{$sw.Stop();$rows+=@{name=$t.name;url=$t.url;ok=$false;status=0;latency_ms=$sw.ElapsedMilliseconds;error=$_.Exception.Message}}}
$now=(Get-Date).ToUniversalTime();$allOk=($rows.ok -notcontains $false)
$doc=@{checked_at=$now.ToString('o');all_ok=$allOk;healthy=@($rows|Where-Object ok).Count;total=$rows.Count;targets=$rows}
$json=$doc|ConvertTo-Json -Depth 6
$json|Set-Content (Join-Path $out 'LATEST.json') -Encoding utf8
$json|Set-Content (Join-Path $history ($now.ToString('yyyyMMdd-HHmmss')+'.json')) -Encoding utf8
$recent=Get-ChildItem $history -Filter '*.json'|Sort-Object LastWriteTime -Descending|Select-Object -First 3
$confirmed=$false;if($recent.Count -ge 2){$confirmed=(@($recent|ForEach-Object{try{(Get-Content $_.FullName -Raw|ConvertFrom-Json).all_ok}catch{$true}}|Where-Object{$_ -eq $false}).Count -ge 2)}
$state=@{state=if($confirmed){'confirmed-degradation'}elseif($allOk){'healthy'}else{'suspected-degradation'};confirmed_degradation=$confirmed;consecutive_required=2;checked_at=$now.ToString('o');failed=@($rows|Where-Object{-not $_.ok}|ForEach-Object{$_.name})}
$state|ConvertTo-Json -Depth 4|Set-Content (Join-Path $out 'ALERT_STATE.json') -Encoding utf8
Get-ChildItem $history -Filter '*.json'|Where-Object LastWriteTime -lt (Get-Date).AddDays(-14)|Remove-Item -Force
@{snapshot=$doc;alert=$state}|ConvertTo-Json -Depth 7
