$ErrorActionPreference='Stop'
$p='D:\HCDecorHUB\HC_Visual_Builder\src\App.tsx'
$s=[IO.File]::ReadAllText($p)
$old="const publish=(kind:'preview'|'publish'|'update')=>{if(!preflight()){alert('Preflight FAIL - production unchanged');return}snapshot('Pre-'+kind);const payload={version:7.2,status:kind==='preview'?'preview':'published',publishedAt:new Date().toISOString(),nodes:clone(nodes)};localStorage.setItem(PKEY,JSON.stringify(payload));setPub(kind==='preview'?'preview':'published');alert(kind.toUpperCase()+' PASS')}"
$new="const publish=(kind:'preview'|'publish'|'update')=>{if(!preflight()){alert('Preflight FAIL - production unchanged');return}snapshot('Pre-'+kind);if(kind==='preview'){const payload={version:7.2,status:'preview',publishedAt:new Date().toISOString(),nodes:clone(nodes)};localStorage.setItem(PKEY,JSON.stringify(payload));setPub('preview');alert('LOCAL PREVIEW SAVED - NOT PUBLIC');return}alert('PUBLISH BLOCKED: WordPress authenticated write and verification are not configured. Production unchanged.')}"
if($s.Contains("PUBLISH BLOCKED: WordPress authenticated write")){Write-Output 'PATCH=ALREADY_APPLIED';exit 0}
if(-not $s.Contains($old)){
  $pattern="const publish=\\(kind:'preview'\\|'publish'\\|'update'\\)=>\\{.*?alert\\(kind\\.toUpperCase\\(\\)\\+' PASS'\\)\\}"
  $matches=[regex]::Matches($s,$pattern,[Text.RegularExpressions.RegexOptions]::Singleline)
  if($matches.Count -ne 1){throw "PATCH_ANCHOR_NOT_FOUND: matches=$($matches.Count)"}
  $old=$matches[0].Value
}
$bak="$p.pre-real-publish-guard.bak"
if(-not(Test-Path $bak)){[IO.File]::Copy($p,$bak)}
$s=$s.Replace($old,$new)
$legacy="localStorage.getItem(PKEY)?'published':'draft'"
if(-not $s.Contains($legacy)){throw 'LEGACY_STATUS_ANCHOR_NOT_FOUND'}
$s=$s.Replace($legacy,"'draft'")
[IO.File]::WriteAllText($p,$s,(New-Object System.Text.UTF8Encoding($false)))
Write-Output 'PATCH=APPLIED'
Write-Output 'GUARD=NO_FALSE_PUBLISH_SUCCESS'
Write-Output 'BACKUP=App.tsx.pre-real-publish-guard.bak'
