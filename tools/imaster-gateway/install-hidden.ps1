param([string]$Root="D:\HCDecorHUB\Gateway")
$ErrorActionPreference="Stop"
New-Item -ItemType Directory -Force -Path $Root | Out-Null
Copy-Item (Join-Path $PSScriptRoot "hc-local-gateway.mjs") (Join-Path $Root "hc-local-gateway.mjs") -Force
$cmd="@echo off`r`ncd /d `"$Root`"`r`nnode hc-local-gateway.mjs >> gateway.log 2>&1"
Set-Content (Join-Path $Root "run.cmd") $cmd -Encoding ASCII
$startup=[Environment]::GetFolderPath("Startup")
$ws=New-Object -ComObject WScript.Shell
$sc=$ws.CreateShortcut((Join-Path $startup "HC Local Gateway.lnk"))
$sc.TargetPath="$env:WINDIR\System32\cmd.exe"
$sc.Arguments='/c ""'+(Join-Path $Root "run.cmd")+'""'
$sc.WorkingDirectory=$Root
$sc.WindowStyle=7
$sc.Save()
"HC_LOCAL_GATEWAY_INSTALLED $Root"
