param([string]$WpRoot="C:\Users\DELL\Local Sites\hcdecor-hub\app\public",[switch]$Watch,[int]$Interval=60)
$ErrorActionPreference="Stop"
[Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12
$ManifestUrl="https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress/hcdecor-sync-manifest.json"
$PluginRoot=Join-Path $WpRoot "wp-content\plugins\hcdecor-core"
function BlobSha([byte[]]$b){$p=[Text.Encoding]::UTF8.GetBytes("blob $($b.Length)"+[char]0);$a=New-Object byte[]($p.Length+$b.Length);[Array]::Copy($p,0,$a,0,$p.Length);[Array]::Copy($b,0,$a,$p.Length,$b.Length);$s=[Security.Cryptography.SHA1]::Create();try{([BitConverter]::ToString($s.ComputeHash($a))).Replace("-","").ToLowerInvariant()}finally{$s.Dispose()}}
function GetBytes([string]$u){$w=New-Object Net.WebClient;$w.Headers["Cache-Control"]="no-cache";$w.Headers["User-Agent"]="HCDecor-Sync/3.0";try{$w.DownloadData($u)}finally{$w.Dispose()}}
function SyncHub{
 Clear-Host;Write-Host "HCDECOR HUB SYNC v3" -ForegroundColor Cyan
 Write-Host ("WordPress: "+$WpRoot);Write-Host ("Plugin   : "+$PluginRoot)
 if(!(Test-Path -LiteralPath $WpRoot)){throw "WordPress path not found: $WpRoot"}
 if(!(Test-Path -LiteralPath $PluginRoot)){New-Item -ItemType Directory -Force -Path $PluginRoot|Out-Null}
 $stamp=[DateTimeOffset]::UtcNow.ToUnixTimeSeconds();$m=[Text.Encoding]::UTF8.GetString((GetBytes ($ManifestUrl+"?t="+$stamp)))|ConvertFrom-Json
 if(!$m.version -or !$m.files -or $m.files.Count -lt 1){throw "Invalid GitHub manifest"}
 Write-Host ("Manifest : "+$m.version+" | "+$m.files.Count+" managed files") -ForegroundColor White
 $updated=0;$verified=0
 foreach($f in $m.files){
  $rel=[string]$f.path;if([string]::IsNullOrWhiteSpace($rel)-or $rel.Contains("..")-or [IO.Path]::IsPathRooted($rel)){throw "Unsafe manifest path: $rel"}
  $expected=([string]$f.git_sha1).ToLowerInvariant();if($expected -notmatch "^[0-9a-f]{40}$"){throw "Invalid SHA: $rel"}
  $target=Join-Path $PluginRoot ($rel.Replace("/","\"))
  if(Test-Path -LiteralPath $target){$local=BlobSha ([IO.File]::ReadAllBytes($target));if($local -eq $expected){$verified++;continue}}
  $sep=if(([string]$f.url).Contains("?")){"&"}else{"?"};$bytes=GetBytes (([string]$f.url)+$sep+"v="+[Uri]::EscapeDataString([string]$m.version)+"&t="+$stamp)
  if((BlobSha $bytes) -ne $expected){throw "Integrity check failed: $rel"}
  $dir=Split-Path -Parent $target;if(!(Test-Path -LiteralPath $dir)){New-Item -ItemType Directory -Force -Path $dir|Out-Null}
  $tmp=$target+".hcnew";[IO.File]::WriteAllBytes($tmp,$bytes)
  try{Move-Item -LiteralPath $tmp -Destination $target -Force}catch{Copy-Item -LiteralPath $tmp -Destination $target -Force;Remove-Item -LiteralPath $tmp -Force}
  if((BlobSha ([IO.File]::ReadAllBytes($target))) -ne $expected){throw "Write verification failed: $rel"}
  Write-Host ("[UPDATED] "+$rel) -ForegroundColor Yellow;$updated++;$verified++
 }
 $critical=@("hcdecor-core.php","recovery-bootstrap.php","modules\background-sync.php","modules\social-manager.php")
 foreach($c in $critical){if(!(Test-Path -LiteralPath (Join-Path $PluginRoot $c))){throw "Critical file missing: $c"}}
 Write-Host "";Write-Host ("[READY] "+$verified+"/"+$m.files.Count+" verified | "+$updated+" updated") -ForegroundColor Green
 Write-Host "Refresh: http://hcdecor-hub.local/wp-admin/" -ForegroundColor Cyan
 Write-Host "After WordPress works, this script is only a recovery tool."
}
do{try{SyncHub}catch{Write-Host "";Write-Host ("[ERROR] "+$_.Exception.Message) -ForegroundColor Red;if(!$Watch){Read-Host "Press Enter to close";exit 1}};if(!$Watch){break};Write-Host ("Next sync in "+$Interval+" seconds. Ctrl+C to stop.");Start-Sleep -Seconds ([Math]::Max(15,$Interval))}while($true)
