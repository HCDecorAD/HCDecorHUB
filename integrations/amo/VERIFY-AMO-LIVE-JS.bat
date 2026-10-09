@echo off
setlocal
set "OUT=D:\HCDecorHUB\HC_Visual_Builder\evidence"
if not exist "%OUT%" mkdir "%OUT%"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference='Stop'; $u='https://amonguyen.hcdecorhub.com/js/amo-shop.js?verify='+[DateTimeOffset]::UtcNow.ToUnixTimeSeconds(); $r=Invoke-WebRequest -UseBasicParsing -Uri $u -TimeoutSec 30 -Headers @{'Cache-Control'='no-cache'}; $s=[string]$r.Content; $ok=($r.StatusCode -eq 200 -and $s.Contains('/api/catalog') -and -not $s.Contains('amo-demo-card') -and -not $s.Contains('shoe-demo-')); $report=@{url=$u;http=$r.StatusCode;catalog=$s.Contains('/api/catalog');fake_demo=$s.Contains('amo-demo-card') -or $s.Contains('shoe-demo-');verified_utc=[DateTime]::UtcNow.ToString('o');pass=$ok}; $report|ConvertTo-Json|Set-Content '%OUT%\amo-live-js-proof.json'; $report|ConvertTo-Json; if(-not $ok){exit 1}"
if errorlevel 1 (echo LIVE_JS_FAIL - check evidence\amo-live-js-proof.json & exit /b 1)
echo LIVE_JS_PASS. THIS DOES NOT CERTIFY BUILDER PUBLISH.
exit /b 0
