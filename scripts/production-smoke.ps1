param([string]$Base='https://hcdecor-hub.huycuongonline.workers.dev')
$ErrorActionPreference='Stop'
$checks=@('/hub/agents','/api/public/status','/api/public/contract','/api/runtime','/api/master/readiness','/api/commerce/status')
$rows=@()
foreach($path in $checks){
 try{
  $r=Invoke-WebRequest -UseBasicParsing ($Base+$path) -TimeoutSec 20
  $status=[int]$r.StatusCode
  $ok=($status -eq 200)
  if($path -eq '/api/public/contract' -and $status -eq 200){
   $j=$r.Content|ConvertFrom-Json
   $ok=($j.contract_version -eq '1.1' -and
        $j.guarantees.default_deny -and
        -not $j.guarantees.production_write -and
        $j.guarantees.approval_required_for_mutation -and
        -not $j.guarantees.secrets_exposed -and
        -not $j.guarantees.test_mode_in_production -and
        $j.authorities.hcdecor -eq 'wordpress' -and
        $j.authorities.gsc -eq 'github-pages' -and
        $j.authorities.amo -eq 'github-pages' -and
        $j.authorities.commerce -eq 'engine-live-data-verified' -and
        $j.blockers.commerce_verified -and
        $j.blockers.custom_domain_verified -and
        $j.persistence.production_state -eq 'durable-provider-required' -and
        -not $j.persistence.local_spool_authority -and
        -not $j.persistence.mutation_execution_enabled)
  }
  if($path -eq '/api/commerce/status' -and $status -eq 200){
   $j=$r.Content|ConvertFrom-Json
   $ok=($j.ready -and $j.catalog.production_authority -and $j.catalog.verified_commerce_data -and -not $j.production_write)
  }
  $rows+=@{path=$path;status=$status;ok=$ok}
 }catch{
  $status=0;if($_.Exception.Response){$status=[int]$_.Exception.Response.StatusCode}
  $rows+=@{path=$path;status=$status;ok=$false;error=$_.Exception.Message}
 }
}
$result=@{ok=(@($rows|Where-Object{-not $_.ok}).Count -eq 0);base=$Base;checks=$rows;checked_at=(Get-Date).ToUniversalTime().ToString('o')}
$result|ConvertTo-Json -Depth 6
if(-not $result.ok){exit 1}
