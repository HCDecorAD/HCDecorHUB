@echo off
setlocal EnableExtensions
set "ROOT=D:\HCDecorHUB\HC_Visual_Builder"
set "OUT=%ROOT%\evidence\amo-source-inspect.txt"
if not exist "%ROOT%\src\App.tsx" (echo BUILDER_NOT_FOUND & exit /b 2)
if not exist "%ROOT%\evidence" mkdir "%ROOT%\evidence"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$s=[IO.File]::ReadAllText('%ROOT%\src\App.tsx');$terms=@('dataBinding','bindData','executeCommand','publish(','PKEY','source:''wordpress''');$lines=@();foreach($term in $terms){$start=0;$count=0;while(($i=$s.IndexOf($term,$start,[StringComparison]::Ordinal)) -ge 0 -and $count -lt 5){$a=[Math]::Max(0,$i-260);$b=[Math]::Min($s.Length,$i+850);$lines+=('=== '+$term+' offset='+$i+' ===');$lines+=$s.Substring($a,$b-$a);$start=$i+$term.Length;$count++}};[IO.File]::WriteAllLines('%OUT%',$lines);Write-Host ('INSPECT_OK snippets='+$lines.Count);Get-Content '%OUT%' | Select-Object -First 35"
if errorlevel 1 (echo INSPECT_FAIL & exit /b 3)
exit /b 0
