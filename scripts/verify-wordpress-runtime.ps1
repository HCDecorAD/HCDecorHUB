$ErrorActionPreference='Stop'
$repo=Split-Path -Parent $PSScriptRoot
$out=Join-Path $repo 'dist\wordpress-runtime'
$zip=Join-Path $out 'hcdecor-hub-runtime.zip'
$release=Join-Path $out 'release.json'
if(!(Test-Path $zip)){throw 'Runtime package missing'}
if(!(Test-Path $release)){throw 'Release manifest missing'}
$m=Get-Content $release -Raw|ConvertFrom-Json
$sha=(Get-FileHash $zip -Algorithm SHA256).Hash.ToLowerInvariant()
if($sha -ne $m.sha256){throw 'Runtime package SHA-256 mismatch'}
if((Get-Item $zip).Length -ne $m.bytes){throw 'Runtime package byte size mismatch'}
$tree=(& 'C:\Program Files\Git\cmd\git.exe' -C $repo rev-parse 'HEAD:wordpress/hcdecor-core').Trim()
if($tree -ne $m.source_tree){throw 'Release source tree does not match WordPress runtime source; rebuild package'}
Add-Type -AssemblyName System.IO.Compression.FileSystem
$z=[IO.Compression.ZipFile]::OpenRead($zip)
try{
 $entries=@($z.Entries|Where-Object{-not [string]::IsNullOrWhiteSpace($_.Name)})
 if($entries.Count -ne $m.files){throw 'Runtime package file count mismatch'}
 foreach($name in @($m.forbidden_files)){
  if($entries.FullName|Where-Object{($_ -replace '\\','/') -match ('(^|/)'+[regex]::Escape($name)+'$')}){throw "Forbidden runtime package file: $name"}
 }
 if(-not($entries.FullName|Where-Object{($_ -replace '\\','/') -match '(^|/)hcdecor-runtime\.php$'})){throw 'Runtime entrypoint missing'}
}finally{$z.Dispose()}
Write-Output 'VERIFY=PASS'
Write-Output ("VERSION="+$m.version)
Write-Output ("FILES="+$m.files)
Write-Output ("SHA256="+$m.sha256)
Write-Output ("SOURCE_TREE="+$m.source_tree)
Write-Output 'PRODUCTION_WRITE=false'
