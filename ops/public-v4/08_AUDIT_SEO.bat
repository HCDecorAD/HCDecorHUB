@echo off
setlocal
set "ROOT=D:\HCDecorHUB\HCDecor_Public_Web_v4"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$bad=@();Get-ChildItem '%ROOT%' -Recurse -Filter *.html|%%{$c=[IO.File]::ReadAllText($_.FullName);if($c -notmatch '<title>.+?</title>'){$bad+=($_.Name+' missing title')};if($c -notmatch '<meta name=["'']description["'']'){$bad+=($_.Name+' missing description')}};if($bad.Count){$bad;exit 2}else{Write-Host 'SEO BASIC AUDIT PASS'}"
