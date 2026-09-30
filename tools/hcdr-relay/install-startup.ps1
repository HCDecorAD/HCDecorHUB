$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\repos\HCDecorHUB'
$src=Join-Path $root 'tools\hcdr-relay\HCDR-Remote-Free.cmd'
$startup=[Environment]::GetFolderPath('Startup')
$dst=Join-Path $startup 'HCDecor-HCDR-Remote-Free.cmd'
Copy-Item -Force $src $dst
Write-Host 'HCDR_STARTUP_INSTALLED' $dst
