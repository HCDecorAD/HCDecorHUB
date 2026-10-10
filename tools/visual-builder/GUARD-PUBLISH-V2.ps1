$ErrorActionPreference='Stop'
$p='D:\HCDecorHUB\HC_Visual_Builder\src\App.tsx'
$s=[IO.File]::ReadAllText($p)
$start="const publish=(kind:'preview'|'publish'|'update')=>"
$end=";const rollback=()=>"
$i=$s.IndexOf($start,[StringComparison]::Ordinal)
if($i -lt 0){throw 'PUBLISH_START_NOT_FOUND'}
$j=$s.IndexOf($end,$i,[StringComparison]::Ordinal)
if($j -lt 0){throw 'ROLLBACK_BOUNDARY_NOT_FOUND'}
if($s.IndexOf($start,$i+1,[StringComparison]::Ordinal) -ge 0){throw 'DUPLICATE_PUBLISH'}
$segment=$s.Substring($i,$j-$i)
if(-not $segment.Contains('localStorage.setItem(PKEY')){throw 'UNEXPECTED_PUBLISH_IMPLEMENTATION'}
$new="const publish=(kind:'preview'|'publish'|'update')=>{if(!preflight()){alert('Preflight FAIL - production unchanged');return}snapshot('Pre-'+kind);if(kind==='preview'){const payload={version:7.2,status:'preview',publishedAt:new Date().toISOString(),nodes:clone(nodes)};localStorage.setItem(PKEY,JSON.stringify(payload));setPub('preview');alert('LOCAL PREVIEW ONLY - NOT PUBLIC');return}alert('PUBLISH BLOCKED: authenticated WordPress write and readback verification required. Production unchanged.')}"
$legacy="localStorage.getItem(PKEY)?'published':'draft'"
if(-not $s.Contains($legacy)){throw 'LEGACY_STATUS_NOT_FOUND'}
$bak="$p.pre-real-publish-guard.bak"
if(-not(Test-Path $bak)){[IO.File]::Copy($p,$bak)}
$s=$s.Substring(0,$i)+$new+$s.Substring($j)
$s=$s.Replace($legacy,"'draft'")
[IO.File]::WriteAllText($p,$s,(New-Object System.Text.UTF8Encoding($false)))
Write-Output 'PATCH=APPLIED'; Write-Output ('OLD_HANDLER_CHARS='+$segment.Length)
