$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\repos\HCDecorHUB'
Set-Location $root
$sha=(git rev-parse --short HEAD).Trim()
Write-Output ("DEPLOY_SOURCE "+$sha)
npm run test:architecture
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
npm run build
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
npx --yes vercel@61.0.0 deploy --prod --yes
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-smoke.ps1
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
Write-Output ("DEPLOY_ACCEPTED "+$sha)
