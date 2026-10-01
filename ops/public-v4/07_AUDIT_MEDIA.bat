@echo off
setlocal
set "ROOT=D:\HCDecorHUB\HCDecor_Public_Web_v4"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$root='%ROOT%';$large=Get-ChildItem $root\assets -Recurse -File -ErrorAction SilentlyContinue|?{$_.Extension -match '\.(png|jpg|jpeg|webp)$' -and $_.Length -gt 800KB};Write-Host ('Large images >800KB: '+$large.Count);$large|Sort Length -Descending|Select -First 20 FullName,@{N='KB';E={[math]::Round($_.Length/1KB)}}|Format-Table -AutoSize;if($large.Count -gt 5){exit 2}"
