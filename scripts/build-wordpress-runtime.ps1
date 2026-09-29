$ErrorActionPreference='Stop'
$repo=Split-Path -Parent $PSScriptRoot
$src=Join-Path $repo 'wordpress\hcdecor-core'
$out=Join-Path $repo 'dist\wordpress-runtime'
$stage=Join-Path $out 'hcdecor-hub-runtime'
$zip=Join-Path $out 'hcdecor-hub-runtime.zip'
if(Test-Path $out){Remove-Item $out -Recurse -Force}
New-Item -ItemType Directory -Path $stage -Force|Out-Null
Copy-Item (Join-Path $src 'hcdecor-runtime.php') $stage
Copy-Item (Join-Path $src 'modules') $stage -Recurse
if(Test-Path (Join-Path $src 'assets')){Copy-Item (Join-Path $src 'assets') $stage -Recurse}
$forbidden=@('homepage-builder.php','hcdecor-core.php','recovery-bootstrap.php')
foreach($name in $forbidden){if(Test-Path (Join-Path $stage $name)){throw "Forbidden runtime package file: $name"}}
$entry=Get-Content (Join-Path $stage 'hcdecor-runtime.php') -Raw
if($entry -notmatch 'Plugin Name: HCDecor HUB Runtime'){throw 'Plugin header missing'}
Compress-Archive -Path $stage -DestinationPath $zip -CompressionLevel Optimal
$files=Get-ChildItem $stage -Recurse -File
Write-Output ("PACKAGE="+$zip)
Write-Output ("FILES="+$files.Count)
Write-Output ("BYTES="+(Get-Item $zip).Length)
