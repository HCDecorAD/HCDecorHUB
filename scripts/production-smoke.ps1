param([string]$Base='https://hcdecor-hub.huycuongonline.workers.dev')
$ErrorActionPreference='Stop'
$checks=@('/hub/agents','/api/public/status','/api/public/contract','/api/runtime','/api/master/readiness','/api/commerce/status')
$rows=@();foreach($path in $checks){try{$r=Invoke-WebRequest -UseBasicParsing ($Base+$path) -TimeoutSec 20;$status=[int]$r.StatusCode;$ok=($status -eq 200);if($path -eq '/api/public/status'){$ok=($status -in 200,207)};if($path -eq '/api/commerce/status'){$ok=($status -in 200,503)};if($path -eq '/api/public/contract' -and $status -eq 200){$j=$r.Content|ConvertFrom-Json;$ok=($j.guarantees.default_deny -and -not $j.guarantees.production_write -and $j.guarantees.approval_required_for_mutation -and $j.authorities.gsc -eq 'cloudflare' -and $j.authorities.amo -eq 'cloudflare')};$rows+=@{path=$path;status=$status;ok=$ok}}catch{if($_.Exception.Response){$status=[int]$_.Exception.Response.StatusCode;$ok=(($path -eq '/api/public/status' -and $status -eq 207) -or ($path -eq '/api/commerce/status' -and $status -eq 503));$rows+=@{path=$path;status=$status;ok=$ok}}else{$rows+=@{path=$path;status=0;ok=$false;error=$_.Exception.Message}}}}
$result=@{ok=(@($rows|Where-Object{-not $_.ok}).Count -eq 0);base=$Base;checks=$rows;checked_at=(Get-Date).ToUniversalTime().ToString('o')}
$result|ConvertTo-Json -Depth 5
if(-not $result.ok){exit 1}
