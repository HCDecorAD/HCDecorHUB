$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\repos\HCDecorHUB'
$stateDir='D:\HCDecorHUB\runtime\deployment'
New-Item -ItemType Directory -Force $stateDir|Out-Null
Set-Location $root
$sha=(git rev-parse --short HEAD).Trim()
$last=Join-Path $stateDir 'LAST_ATTEMPT.json'
if(Test-Path $last){
 try{$x=Get-Content $last -Raw|ConvertFrom-Json;if($x.reason -eq 'vercel-daily-quota' -and ([datetime]$x.retry_after_utc) -gt (Get-Date).ToUniversalTime()){Write-Output ('DEPLOY_SKIPPED quota cooldown until '+$x.retry_after_utc);exit 75}}catch{}
}
Write-Output ("DEPLOY_SOURCE "+$sha)
npm run test:architecture;if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
npm run build;if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
$out=& npx --yes vercel@61.0.0 deploy --prod --yes 2>&1
$code=$LASTEXITCODE;$out|ForEach-Object{Write-Output $_}
if($code -ne 0){
 $txt=($out -join [Environment]::NewLine)
 if($txt -match 'api-deployments-free-per-day|more than 100'){
  $retry=(Get-Date).ToUniversalTime().AddHours(24)
  @{ok=$false;reason='vercel-daily-quota';source=$sha;attempted_at=(Get-Date).ToUniversalTime().ToString('o');retry_after_utc=$retry.ToString('o')}|ConvertTo-Json|Set-Content $last -Encoding utf8
  Write-Output ('DEPLOY_BLOCKED quota; cooldown until '+$retry.ToString('o'));exit 75
 }
 exit $code
}
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-smoke.ps1;if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
@{ok=$true;reason='deployed';source=$sha;attempted_at=(Get-Date).ToUniversalTime().ToString('o')}|ConvertTo-Json|Set-Content $last -Encoding utf8
Write-Output ("DEPLOY_ACCEPTED "+$sha)
