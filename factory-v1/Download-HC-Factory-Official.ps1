$ErrorActionPreference = 'Stop'
$dest='D:\HCDecorHUB\HC Website Factory\10-Downloads'
New-Item -ItemType Directory -Force -Path $dest | Out-Null
foreach($slug in @('elementor','woocommerce','astra-sites','members','updraftplus','mainwp')) {
 try {
  $f=Join-Path $dest "$slug.zip"
  Invoke-WebRequest "https://downloads.wordpress.org/plugin/$slug.latest-stable.zip" -OutFile $f -TimeoutSec 120
  Write-Host "[OK] $slug SHA256=$((Get-FileHash $f -Algorithm SHA256).Hash)"
 } catch { Write-Warning "[FAIL] $slug : $_" }
}
try {
 $f=Join-Path $dest 'astra-theme.zip'
 Invoke-WebRequest 'https://downloads.wordpress.org/theme/astra.latest-stable.zip' -OutFile $f -TimeoutSec 120
 Write-Host "[OK] Astra SHA256=$((Get-FileHash $f -Algorithm SHA256).Hash)"
} catch { Write-Warning "[FAIL] Astra: $_" }
Write-Host 'Download only; nothing installed.'
