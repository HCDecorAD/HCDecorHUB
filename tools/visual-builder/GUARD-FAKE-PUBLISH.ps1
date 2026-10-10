$ErrorActionPreference='Stop'
$p='D:\HCDecorHUB\HC_Visual_Builder\src\App.tsx'
$s=[IO.File]::ReadAllText($p)
if($s.Contains('PUBLISH BLOCKED: authenticated backend')){Write-Output 'PATCH=ALREADY_APPLIED';exit 0}
$startMarker="const publish=(kind:'preview'|'publish'|'update')=>"
$endMarker='const rollback='
$start=$s.IndexOf($startMarker,[StringComparison]::Ordinal)
if($start -lt 0){throw 'PUBLISH_START_NOT_FOUND'}
$end=$s.IndexOf($endMarker,$start,[StringComparison]::Ordinal)
if($end -lt 0 -or ($end-$start) -gt 3000){throw 'PUBLISH_END_NOT_FOUND_OR_TOO_FAR'}
$old=$s.Substring($start,$end-$start)
if(-not $old.Contains('localStorage.setItem(PKEY') -or -not $old.Contains('setPub(')){throw 'PUBLISH_SHAPE_UNEXPECTED'}
$legacy="localStorage.getItem(PKEY)?'published':'draft'"
if(-not $s.Contains($legacy)){throw 'LEGACY_STATUS_ANCHOR_NOT_FOUND'}
$new="const publish=(kind:'preview'|'publish'|'update')=>{if(!preflight()){alert('Preflight FAIL - production unchanged');return}if(kind==='preview'){snapshot('Pre-preview');const payload={version:7.2,status:'preview',publishedAt:new Date().toISOString(),nodes:clone(nodes)};localStorage.setItem(PKEY,JSON.stringify(payload));setPub('preview');alert('LOCAL PREVIEW SAVED - NOT PUBLIC');return}alert('PUBLISH BLOCKED: authenticated backend and verification required. Production unchanged.')} ;"
$updated=$s.Substring(0,$start)+$new+$s.Substring($end)
$updated=$updated.Replace($legacy,"'draft'")
$bak="$p.pre-real-publish-guard.bak"
if(-not(Test-Path $bak)){[IO.File]::Copy($p,$bak)}
[IO.File]::WriteAllText($p,$updated,(New-Object System.Text.UTF8Encoding($false)))
Write-Output 'PATCH=APPLIED'
Write-Output 'GUARD=NO_FALSE_PUBLISH_SUCCESS'
Write-Output 'BACKUP=App.tsx.pre-real-publish-guard.bak'
