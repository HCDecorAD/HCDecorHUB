param([string]$Repo=$env:HCDR_RELAY_REPO,[int]$TimeoutSec=120)
$ErrorActionPreference='Stop'
if(-not $Repo){$Repo='HCDecorAD/HCDecor-HCDR-Relay'}
$root=Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $root
node scripts/hcdr-relay-implementation.test.mjs
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
try{gh auth status 2>$null|Out-Null}catch{Write-Host 'HC_DONE_D02_OWNER_REQUIRED gh_auth_missing=1';exit 30}
$mission='hc-group-hcdr-live-proof';$corr=[guid]::NewGuid().ToString();$source='hc-group-done-program'
$body=@{schema='hcdr-relay/v1.2';source='hocuong-v12-production';approved=$true;approval_scope='HC Group DONE harmless read-only HCDR live health proof';tool='health';args=@{};source_id=$source;mission_id=$mission;correlation_id=$corr}|ConvertTo-Json -Compress
$url=gh issue create --repo $Repo --title "HCDR live proof $corr" --label hcdr-job --body $body
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
$num=($url -split '/')[-1]
$deadline=(Get-Date).AddSeconds($TimeoutSec)
while((Get-Date)-lt $deadline){
  Start-Sleep -Seconds 5
  $raw=gh api "repos/$Repo/issues/$num/comments"
  if($LASTEXITCODE -ne 0){continue}
  $comments=$raw|ConvertFrom-Json
  foreach($c in $comments){
    $text=[string]$c.body
    if($text -match '(?s)^\s*```json\s*(\{.*\})\s*```\s*$'){$text=$matches[1]}
    elseif($text -match '(?s)^\s*```\s*(\{.*\})\s*```\s*$'){$text=$matches[1]}
    try{$j=$text|ConvertFrom-Json}catch{continue}
    if($j.schema -eq 'hcdr-result/v2' -and $j.correlation_id -eq $corr -and $j.mission_id -eq $mission -and $j.source_id -eq $source){
      $outDir=Join-Path $root '.runtime\remaining-done';New-Item -ItemType Directory -Force -Path $outDir|Out-Null
      $j|ConvertTo-Json -Depth 10|Set-Content -Encoding UTF8 (Join-Path $outDir 'D02-hcdr-live.json')
      Write-Host "HC_DONE_D02_PASS live_hcdr_roundtrip=1 correlation=$corr";exit 0
    }
  }
}
Write-Host "HC_DONE_D02_OWNER_REQUIRED relay_timeout=1 issue=$num";exit 30
