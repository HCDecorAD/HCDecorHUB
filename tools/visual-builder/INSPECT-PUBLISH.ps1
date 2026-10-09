$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\HC_Visual_Builder'
$dest='D:\HCDecorHUB\evidence\hc-visual-builder'
New-Item -ItemType Directory -Force -Path $dest | Out-Null
$files=@(Get-ChildItem (Join-Path $root 'src') -File -Recurse -Include '*.tsx','*.ts' -ErrorAction Stop)
$matches=@($files|Select-String -Pattern 'localStorage|publish|wp-json|fetch\(' -ErrorAction Stop|Select-Object -First 100|ForEach-Object {[ordered]@{file=$_.Path.Replace($root+'\','');line=$_.LineNumber;snippet=$_.Line.Trim().Substring(0,[Math]::Min(220,$_.Line.Trim().Length))}})
$matches|ConvertTo-Json -Depth 5|Set-Content (Join-Path $dest 'publish-source-inspect.json') -Encoding UTF8
Write-Output ('FILES='+$files.Count)
Write-Output ('MATCHES='+$matches.Count)
$matches|Select-Object -First 25|ForEach-Object {Write-Output ($_.file+':'+$_.line+':'+$_.snippet)}
Write-Output 'EVIDENCE=publish-source-inspect.json'
