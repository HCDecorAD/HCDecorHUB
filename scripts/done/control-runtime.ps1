$ErrorActionPreference='Stop'
$root=Split-Path -Parent (Split-Path -Parent $PSScriptRoot);Set-Location $root
npm run test:agent-control-contract
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
npm run test:autochat-contract
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
if(-not (Test-Path 'scripts\hc-agent-control-runtime.test.mjs') -or -not (Test-Path 'scripts\hc-autochat-runtime.test.mjs')){
 Write-Host 'HC_DONE_D04_NOT_DONE runtime_implementation_missing=1';exit 20
}
node scripts/hc-agent-control-runtime.test.mjs
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
node scripts/hc-autochat-runtime.test.mjs
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
Write-Host 'HC_DONE_D04_PASS control_runtime=1'
