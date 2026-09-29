$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB'
$stamp=Get-Date -Format 'yyyyMMdd-HHmmss'
$dest=Join-Path $root ('runtime\backups\'+$stamp)
New-Item -ItemType Directory -Force $dest | Out-Null
$items=@('config','docs','scripts','lib','app','wordpress')
foreach($x in $items){$src=Join-Path (Join-Path $root 'repos\HCDecorHUB') $x;if(Test-Path $src){Copy-Item $src (Join-Path $dest $x) -Recurse -Force}}
Copy-Item (Join-Path $root 'runtime\checkpoints\LATEST.md') (Join-Path $dest 'CHECKPOINT.md') -Force
$manifest=Get-ChildItem $dest -Recurse -File | Get-FileHash -Algorithm SHA256 | Select-Object Path,Hash
$manifest|ConvertTo-Json -Depth 4|Set-Content (Join-Path $dest 'SHA256.json') -Encoding utf8
@{ok=$true;backup=$dest;files=$manifest.Count;created_at=(Get-Date).ToUniversalTime().ToString('o')}|ConvertTo-Json
