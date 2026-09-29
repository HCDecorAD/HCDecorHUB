$ErrorActionPreference='Stop'
$repo=Split-Path -Parent $PSScriptRoot
$src=Join-Path $repo 'wordpress\hcdecor-core'
$out=Join-Path $repo 'dist\wordpress-runtime'
$stage=Join-Path $out 'hcdecor-hub-runtime'
$zip=Join-Path $out 'hcdecor-hub-runtime.zip'
$release=Join-Path $out 'release.json'
if(Test-Path $out){Remove-Item $out -Recurse -Force}
New-Item -ItemType Directory -Path $stage -Force|Out-Null
Copy-Item (Join-Path $src 'hcdecor-runtime.php') $stage
Copy-Item (Join-Path $src 'modules') $stage -Recurse
if(Test-Path (Join-Path $src 'assets')){Copy-Item (Join-Path $src 'assets') $stage -Recurse}
$forbidden=@('homepage-builder.php','hcdecor-core.php','recovery-bootstrap.php')
foreach($name in $forbidden){if(Test-Path (Join-Path $stage $name)){throw "Forbidden runtime package file: $name"}}
$entry=Get-Content (Join-Path $stage 'hcdecor-runtime.php') -Raw
if($entry -notmatch 'Plugin Name: HCDecor HUB Runtime'){throw 'Plugin header missing'}
$version=([regex]::Match($entry,'Version:\s*([^\r\n]+)')).Groups[1].Value.Trim()
if(-not $version){throw 'Plugin version missing'}
Compress-Archive -Path $stage -DestinationPath $zip -CompressionLevel Optimal
$files=Get-ChildItem $stage -Recurse -File
$sha=(Get-FileHash $zip -Algorithm SHA256).Hash.ToLowerInvariant()
$git=(& 'C:\Program Files\Git\cmd\git.exe' -C $repo rev-parse HEAD).Trim()
$meta=[ordered]@{package='hcdecor-hub-runtime.zip';version=$version;sha256=$sha;files=$files.Count;bytes=(Get-Item $zip).Length;source_commit=$git;production_write=$false;requires_approval=$true;forbidden_files=$forbidden}
[IO.File]::WriteAllText($release,($meta|ConvertTo-Json -Depth 4),(New-Object Text.UTF8Encoding($false)))
Write-Output ("PACKAGE="+$zip)
Write-Output ("VERSION="+$version)
Write-Output ("FILES="+$files.Count)
Write-Output ("BYTES="+(Get-Item $zip).Length)
Write-Output ("SHA256="+$sha)
Write-Output ("RELEASE="+$release)
