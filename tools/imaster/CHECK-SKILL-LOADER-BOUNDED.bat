@echo off
setlocal
set "R=D:\HCDecorHUB\repos\HCDecorHUB"
set "OUT=D:\HCDecorHUB\evidence\imaster-skill-binding"
if not exist "%OUT%" mkdir "%OUT%"
powershell -NoProfile -Command "$ErrorActionPreference='Stop';$r='%R%';$dirs=@('tools\hcdr-relay','tools\imaster','imaster','src','runtime')|ForEach-Object{Join-Path $r $_}|Where-Object{Test-Path $_};$files=@(foreach($d in $dirs){Get-ChildItem $d -File -Recurse -ErrorAction SilentlyContinue|Where-Object{$_.Extension -in '.ps1','.mjs','.js','.json','.cmd','.bat' -and $_.FullName -notmatch 'node_modules|\\dist\\|\\build\\'}|Select-Object -First 200});$hits=@($files|Select-String -Pattern 'AGENTS\.md|SKILL\.md|skill.loader|skill_registry|hcdr-autorecovery' -List -ErrorAction SilentlyContinue|Select-Object -First 30 -ExpandProperty Path);[ordered]@{scan_files=$files.Count;hits=$hits;runtime_loader='UNVERIFIED';at=(Get-Date).ToUniversalTime().ToString('o')}|ConvertTo-Json -Depth 4|Set-Content '%OUT%\loader-discovery.json' -Encoding UTF8;Write-Output ('FILES='+$files.Count);$hits|ForEach-Object{Write-Output ('HIT='+$_)}"
if errorlevel 1 exit /b 30
echo EVIDENCE=%OUT%\loader-discovery.json
exit /b 0
