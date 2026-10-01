@echo off
setlocal
set "ROOT=D:\HCDecorHUB\HCDecor_Public_Web_v4"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$root='%ROOT%';$bad=@();Get-ChildItem $root -Recurse -Filter *.html|%%{$f=$_;$c=[IO.File]::ReadAllText($f.FullName);[regex]::Matches($c,'(?:href|src)=["'']([^"''#]+)["'']')|%%{$u=$_.Groups[1].Value;if($u -notmatch '^(https?:|mailto:|tel:|data:|javascript:)'){$p=Join-Path $f.DirectoryName ($u -replace '/','\');if(!(Test-Path $p) -and !(Test-Path ($p+'.html'))){$bad+=($f.Name+' -> '+$u)}}}};if($bad.Count){$bad;exit 2}else{Write-Host 'LINK AUDIT PASS'}"
