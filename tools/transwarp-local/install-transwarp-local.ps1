param([string]$RepoRoot="D:\HCDecorHUB\repos\HCDecorHUB",[string]$DataRoot="D:\HC_DATA\queue\transwarp")
$ErrorActionPreference="Stop"
$src=Join-Path $RepoRoot "tools\transwarp-local\transwarp-local-runner.ps1"
if(-not (Test-Path $src)){throw ("RUNNER_SOURCE_NOT_FOUND: "+$src)}
$install="D:\HCDecorHUB\HC TransWarp Infrastructure\runtime\local-runner"
New-Item -ItemType Directory -Force -Path $install|Out-Null
$runner=Join-Path $install "transwarp-local-runner.ps1"
Copy-Item $src $runner -Force
@("inbox","running","done","failed","logs","evidence")|ForEach-Object{New-Item -ItemType Directory -Force -Path (Join-Path $DataRoot $_)|Out-Null}
$startup=Join-Path $env:APPDATA "Microsoft\Windows\Start Menu\Programs\Startup\HC-TransWarp-Local.cmd"
$cmd="@echo off`r`nstart `"`" powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$runner`" -DataRoot `"$DataRoot`"`r`n"
Set-Content $startup -Value $cmd -Encoding ASCII
Start-Process powershell.exe -ArgumentList "-NoProfile","-WindowStyle","Hidden","-ExecutionPolicy","Bypass","-File",$runner,"-DataRoot",$DataRoot -WindowStyle Hidden
$health=[ordered]@{schema="transwarp-local/health-v1";installed_at=(Get-Date).ToString("o");install_root=$install;data_root=$DataRoot;startup=$startup;mode="LOCAL_DIRECT_RUNNER";github="SYNC_ONLY";hcdr="FALLBACK_ONLY"}
$health|ConvertTo-Json -Depth 5|Set-Content (Join-Path $install "health.json") -Encoding UTF8
Write-Output "TRANSWARP_LOCAL_INSTALL_PASS"
Write-Output ("RUNNER="+$runner)
Write-Output ("DATA="+$DataRoot)
Write-Output ("STARTUP="+$startup)