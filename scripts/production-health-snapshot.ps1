$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB'
$out=Join-Path $root 'runtime\monitoring'
New-Item -ItemType Directory -Force $out | Out-Null
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
$rows=@(); foreach($t in $targets){try{$r=Invoke-WebRequest -UseBasicParsing $t.url -TimeoutSec 20;$rows+=@{name=$t.name;url=$t.url;ok=($r.StatusCode -eq 200);status=[int]$r.StatusCode;bytes=$r.Content.Length}}catch{$rows+=@{name=$t.name;url=$t.url;ok=$false;status=0;error=$_.Exception.Message}}}
$doc=@{checked_at=(Get-Date).ToUniversalTime().ToString('o');all_ok=($rows.ok -notcontains $false);targets=$rows}
$doc|ConvertTo-Json -Depth 6|Set-Content (Join-Path $out 'LATEST.json') -Encoding utf8
$doc|ConvertTo-Json -Depth 6
