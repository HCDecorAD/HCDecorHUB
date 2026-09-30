param([string]$Root="D:\HCDecorHUB")
$ErrorActionPreference="Continue"
$hub=Join-Path $Root "repos\HCDecorHUB";$engine=Join-Path $Root "repos\AMONguyen";$out=Join-Path $Root "runtime\monitoring\commerce-readiness.json"
$checks=@()
function AddCheck($name,$ok,$detail){$script:checks += [pscustomobject]@{name=$name;ok=[bool]$ok;detail=$detail}}
Set-Location $hub
node scripts\commerce-contract-check.mjs *> $env:TEMP\hc-contract.txt;AddCheck "hub_contract" ($LASTEXITCODE-eq 0) ((Get-Content $env:TEMP\hc-contract.txt -Tail 1)-join" ")
npm run test:architecture *> $env:TEMP\hc-arch.txt;AddCheck "architecture" ($LASTEXITCODE-eq 0) ((Get-Content $env:TEMP\hc-arch.txt|Select-String "ARCHITECTURE PASS"|Select-Object -Last 1)-join" ")
npm run build *> $env:TEMP\hc-build.txt;AddCheck "hub_build" ($LASTEXITCODE-eq 0) "Next production build"
git diff --check *> $null;AddCheck "hub_diff" ($LASTEXITCODE-eq 0) "git diff --check"
$admin=Get-Content lib\commerce\admin.js -Raw;AddCheck "production_write_lock" ($admin.Contains("production_write:false")) "production_write=false";AddCheck "publish_excluded" (-not $admin.Contains('"product-draft-publish"')) "No product draft publish action"
Set-Location $engine
node --check shop-engine\src\index.js *> $null;AddCheck "engine_syntax" ($LASTEXITCODE-eq 0) "node --check"
git diff --check *> $null;AddCheck "engine_diff" ($LASTEXITCODE-eq 0) "git diff --check"
$pass=($checks|Where-Object ok).Count;$total=$checks.Count;$report=[pscustomobject]@{generated_at=(Get-Date).ToString("o");scope="local_only";production_mutation=$false;catalog_authority_verified=$false;status=$(if($pass-eq$total){"LOCAL_READY_PRODUCTION_BLOCKED"}else{"LOCAL_CHECK_FAILED"});summary=[pscustomobject]@{pass=$pass;total=$total};checks=$checks;blockers=@("catalog_authority_unverified","production_approval_required")}
$report|ConvertTo-Json -Depth 6|Set-Content -Encoding UTF8 $out
Write-Host "COMMERCE_READINESS $pass/$total $($report.status)"
if($pass-ne$total){exit 9}
