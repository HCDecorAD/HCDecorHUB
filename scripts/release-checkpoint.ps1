param([string]$Root='D:\HCDecorHUB')
$ErrorActionPreference='Stop'
$repo=Join-Path $Root 'repos\HCDecorHUB';$backupRoot=Join-Path $Root 'runtime\backups'
if(!(Test-Path $repo)){throw 'repo_missing'}
Set-Location $repo
$sha=(git rev-parse HEAD).Trim();if($LASTEXITCODE){throw 'git_head_failed'}
$stamp=Get-Date -Format 'yyyyMMdd-HHmmss';$dest=Join-Path $backupRoot $stamp
New-Item -ItemType Directory -Force $dest|Out-Null
foreach($x in @('config','docs','scripts','lib','app','wordpress')){$src=Join-Path $repo $x;if(Test-Path $src){Copy-Item $src (Join-Path $dest $x) -Recurse -Force}}
Set-Content (Join-Path $dest 'SOURCE_SHA.txt') $sha
$m=Get-ChildItem $dest -Recurse -File|Get-FileHash -Algorithm SHA256|Select-Object Path,Hash
$m|ConvertTo-Json -Depth 4|Set-Content (Join-Path $dest 'SHA256.json') -Encoding utf8
& (Join-Path $repo 'scripts\verify-latest-backup.ps1')
if($LASTEXITCODE){throw 'backup_verify_failed'}
@{ok=$true;sha=$sha;backup=$dest;files=$m.Count;created_at=(Get-Date).ToUniversalTime().ToString('o')}|ConvertTo-Json
