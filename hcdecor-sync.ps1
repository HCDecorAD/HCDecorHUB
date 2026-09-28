param([Parameter(Mandatory=$true)][string]$WpRoot,[switch]$Watch,[int]$Interval=60)
$ErrorActionPreference="Stop"
[Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12
$RepoApi="https://api.github.com/repos/HCDecorAD/HCDecorHUB"
$WpRoot=[IO.Path]::GetFullPath($WpRoot)
if($WpRoot -match '^\\\\' -or $WpRoot -match '^(?i:https?:)'){throw "WpRoot must be an explicit local filesystem path."}
$PluginRoot=Join-Path $WpRoot "wp-content\plugins\hcdecor-core"
function BlobSha([byte[]]$b){$p=[Text.Encoding]::UTF8.GetBytes("blob $($b.Length)"+[char]0);$a=New-Object byte[]($p.Length+$b.Length);[Array]::Copy($p,0,$a,0,$p.Length);[Array]::Copy($b,0,$a,$p.Length,$b.Length);$s=[Security.Cryptography.SHA1]::Create();try{([BitConverter]::ToString($s.ComputeHash($a))).Replace("-","").ToLowerInvariant()}finally{$s.Dispose()}}
function GetBytes([string]$u,[string]$accept="application/vnd.github.raw+json"){$w=New-Object Net.WebClient;$w.Headers["Cache-Control"]="no-cache";$w.Headers["User-Agent"]="HCDecor-Sync/5.0";$w.Headers["Accept"]=$accept;try{$w.DownloadData($u)}finally{$w.Dispose()}}
function SyncHub{
 Write-Host "HCDECOR LOCAL RECOVERY SYNC v5" -ForegroundColor Cyan
 Write-Host ("Local WordPress: "+$WpRoot);Write-Host ("Local plugin   : "+$PluginRoot)
 if(!(Test-Path -LiteralPath $WpRoot)){throw "Local WordPress path not found: $WpRoot"}
 if(!(Test-Path -LiteralPath (Join-Path $WpRoot "wp-content"))){throw "Not a local WordPress root (wp-content missing): $WpRoot"}
 if(!(Test-Path -LiteralPath $PluginRoot)){New-Item -ItemType Directory -Force -Path $PluginRoot|Out-Null}
 $commit=([Text.Encoding]::UTF8.GetString((GetBytes ($RepoApi+"/commits/main") "application/vnd.github+json"))|ConvertFrom-Json).sha;if($commit -notmatch "^[0-9a-f]{40}$"){throw "Cannot resolve GitHub main commit"}
 $m=[Text.Encoding]::UTF8.GetString((GetBytes ($RepoApi+"/contents/wordpress/hcdecor-sync-manifest.json?ref="+$commit)))|ConvertFrom-Json
 if(!$m.version -or !$m.files -or $m.files.Count -lt 1){throw "Invalid GitHub manifest"}
 Write-Host ("Commit   : "+$commit);Write-Host ("Manifest : "+$m.version+" | "+$m.files.Count+" managed files")
 $updated=0;$verified=0
 foreach($f in $m.files){$rel=[string]$f.path;if([string]::IsNullOrWhiteSpace($rel)-or $rel.Contains("..")-or [IO.Path]::IsPathRooted($rel)){throw "Unsafe manifest path: $rel"};$expected=([string]$f.git_sha1).ToLowerInvariant();if($expected -notmatch "^[0-9a-f]{40}$"){throw "Invalid SHA: $rel"};$target=Join-Path $PluginRoot ($rel.Replace("/","\"));if(Test-Path -LiteralPath $target){$local=BlobSha ([IO.File]::ReadAllBytes($target));if($local -eq $expected){$verified++;continue}};$repoPath="wordpress/hcdecor-core/"+$rel;$bytes=GetBytes ($RepoApi+"/contents/"+$repoPath+"?ref="+$commit);if((BlobSha $bytes) -ne $expected){throw "Integrity check failed: $rel"};$dir=Split-Path -Parent $target;if(!(Test-Path -LiteralPath $dir)){New-Item -ItemType Directory -Force -Path $dir|Out-Null};$tmp=$target+".hcnew";[IO.File]::WriteAllBytes($tmp,$bytes);Move-Item -LiteralPath $tmp -Destination $target -Force;if((BlobSha ([IO.File]::ReadAllBytes($target))) -ne $expected){throw "Write verification failed: $rel"};Write-Host ("[UPDATED] "+$rel) -ForegroundColor Yellow;$updated++;$verified++}
 $critical=@("hcdecor-core.php","recovery-bootstrap.php","modules\background-sync.php","modules\social-manager.php");foreach($c in $critical){if(!(Test-Path -LiteralPath (Join-Path $PluginRoot $c))){throw "Critical file missing: $c"}}
 Write-Host ("[READY] "+$verified+"/"+$m.files.Count+" verified | "+$updated+" updated") -ForegroundColor Green
 Write-Host "Local recovery only. This script does not deploy HCDecor production."
}
do{try{SyncHub}catch{Write-Host ("[ERROR] "+$_.Exception.Message) -ForegroundColor Red;exit 1};if(!$Watch){break};Start-Sleep -Seconds ([Math]::Max(15,$Interval))}while($true)
