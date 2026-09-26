$ErrorActionPreference = "Stop"
Write-Host "HCDecor Local installer" -ForegroundColor Cyan
if (-not (Get-Command wp -ErrorAction SilentlyContinue)) {
  throw "WP-CLI not found. Open Local > HCDecor HUB > Site shell, then run this script there."
}
bash ./setup-hcdecor.sh
