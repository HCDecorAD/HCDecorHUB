$ErrorActionPreference='Stop'
$p='D:\HCDecorHUB\HC_Visual_Builder\src\App.tsx'
$s=[IO.File]::ReadAllText($p)
$old="const publish=(kind:'preview'|'publish'|'update')=>{if(!preflight()){alert('Preflight FAIL - production unchanged');return}snapshot('Pre-'+kind);const payload={version:7.2,status:kind==='preview'?'preview':'published',publishedAt:new Date().toISOString(),nodes:clone(nodes)};localStorage.setItem(PKEY,JSON.stringify(payload));setPub(kind==='preview'?'preview':'published');alert(kind.toUpperCase()+' PASS')}"
$new="const publish=(kind:'preview'|'publish'|'update')=>{if(!preflight()){alert('Preflight FAIL - production unchanged');return}snapshot('Pre-'+kind);if(kind==='preview'){const payload={version:7.2,status:'preview',publishedAt:new Date().toISOString(),nodes:clone(nodes)};localStorage.setItem(PKEY,JSON.stringify(payload));setPub('preview');alert('LOCAL PREVIEW SAVED - NOT PUBLIC');return}alert('PUBLISH BLOCKED: WordPress authenticated write and verification are not configured. Production unchanged.')}"
if(-not $s.Contains($old)){throw 'PATCH_ANCHOR_NOT_FOUND'}
$bak="$p.pre-real-publish-guard.bak"
if(-not(Test-Path $bak)){[IO.File]::Copy($p,$bak)}
$s=$s.Replace($old,$new)
[IO.File]::WriteAllText($p,$s,(New-Object System.Text.UTF8Encoding($false)))
Write-Output 'PATCH=APPLIED'
Write-Output 'GUARD=NO_FALSE_PUBLISH_SUCCESS'
Write-Output 'BACKUP=App.tsx.pre-real-publish-guard.bak'
