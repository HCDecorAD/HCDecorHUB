$ErrorActionPreference='Stop'
$base='https://hcdecorhub.vercel.app'
$checks=@(
 @{path='/hub/agents';kind='html'},
 @{path='/api/public/status';kind='json'},
 @{path='/api/public/contract';kind='json'},
 @{path='/api/runtime';kind='json'},
 @{path='/api/master/readiness';kind='json'},
 @{path='/api/commerce/status';kind='json'}
)
$rows=@();foreach($c in $checks){try{$r=Invoke-WebRequest -UseBasicParsing ($base+$c.path) -TimeoutSec 20;$ok=$r.StatusCode -eq 200;if($c.path -eq '/api/public/contract' -and $ok){$j=$r.Content|ConvertFrom-Json;$ok=($j.guarantees.default_deny -and -not $j.guarantees.production_write -and $j.guarantees.approval_required_for_mutation)};$rows+=@{path=$c.path;status=[int]$r.StatusCode;ok=$ok}}catch{$rows+=@{path=$c.path;status=0;ok=$false;error=$_.Exception.Message}}}
$result=@{ok=(@($rows|Where-Object{-not $_.ok}).Count -eq 0);checks=$rows;checked_at=(Get-Date).ToUniversalTime().ToString('o')}
$result|ConvertTo-Json -Depth 5
if(-not $result.ok){exit 1}
