param([string]$BackupRoot='D:\HCDecorHUB\runtime\backups')
$ErrorActionPreference='Stop'
$b=Get-ChildItem -LiteralPath $BackupRoot -Directory|Sort-Object LastWriteTime -Descending|Select-Object -First 1
if(-not $b){throw 'no_backup'}
$m=Get-Content -LiteralPath (Join-Path $b.FullName 'SHA256.json') -Raw|ConvertFrom-Json
$bad=@();foreach($x in $m){if(Test-Path -LiteralPath $x.Path){$h=(Get-FileHash -LiteralPath $x.Path -Algorithm SHA256).Hash;if($h -ne $x.Hash){$bad+=$x.Path}}else{$bad+=$x.Path}}
$r=@{ok=($bad.Count -eq 0);backup=$b.Name;entries=$m.Count;mismatches=$bad.Count;checked_at=(Get-Date).ToUniversalTime().ToString('o')}
$r|ConvertTo-Json
if(-not $r.ok){exit 1}
