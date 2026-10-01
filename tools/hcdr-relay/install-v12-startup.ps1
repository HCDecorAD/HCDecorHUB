$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\repos\HCDecorHUB'
$src=Join-Path $root 'tools\hcdr-relay\HCDR-V12-Test.cmd'
$startup=[Environment]::GetFolderPath('Startup')
$dst=Join-Path $startup 'HCDecor-HCDR-V12-Test.cmd'
Copy-Item -Force $src $dst
Write-Host 'HCDR_V12_STARTUP_INSTALLED' $dst
