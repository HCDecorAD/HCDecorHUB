$ErrorActionPreference='Stop'
$root=Split-Path -Parent (Split-Path -Parent $PSScriptRoot);Set-Location $root
npm run test:mediaflow-contract; if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
npm run test:video-downloader-contract; if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
npm run test:design-ai-contract; if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
$needed=@('scripts\hc-mediaflow-runtime.test.mjs','scripts\hc-video-downloader-runtime.test.mjs','scripts\hc-design-ai-runtime.test.mjs')
$missing=@($needed|Where-Object{-not(Test-Path $_)})
if($missing.Count){Write-Host "HC_DONE_D05_NOT_DONE runtime_implementation_missing=$($missing -join ',')";exit 20}
foreach($f in $needed){node $f;if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}}
Write-Host 'HC_DONE_D05_PASS media_runtime_bundle=1'
