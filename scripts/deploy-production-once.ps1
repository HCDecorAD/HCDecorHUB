$ErrorActionPreference='Stop'
Set-Location 'D:\HCDecorHUB\repos\HCDecorHUB'
$sha=(git rev-parse --short HEAD).Trim();Write-Output ("CLOUDFLARE_DEPLOY_SOURCE "+$sha)
npm run test:architecture;if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
npm run cf:build;if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
npx wrangler deploy;if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-smoke.ps1;if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
Write-Output ("CLOUDFLARE_DEPLOY_ACCEPTED "+$sha)
