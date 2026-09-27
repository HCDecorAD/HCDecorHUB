param([string]$WpRoot="C:\Users\DELL\Local Sites\hcdecor-hub\app\public",[switch]$Watch,[int]$Interval=60)
$ErrorActionPreference="Stop"
$ManifestUrl="https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress/hcdecor-sync-manifest.json"
$PluginRoot=Join-Path $WpRoot "wp-content\plugins\hcdecor-core"
function BlobSha([byte[]]$b){$p=[Text.Encoding]::UTF8.GetBytes("blob $($b.Length)"+[char]0);$a=New-Object byte[] ($p.Length+$b.Length);[Array]::Copy($p,0,$a,0,$p.Length);[Array]::Copy($b,0,$a,$p.Length,$b.Length);$s=[Security.Cryptography.SHA1]::Create();try{return ([BitConverter]::ToString($s.ComputeHash($a))).Replace("-","").ToLower()}finally{$s.Dispose()}}
function GetBytes($u){$w=New-Object Net.WebClient;$w.Headers["Cache-Control"]="no-cache";$w.Headers["User-Agent"]="HCDecor-Recovery-Sync";try{return $w.DownloadData($u)}finally{$w.Dispose()}}
function SyncHub {
 Write-Host "=== HCDECOR RECOVERY SYNC ===" -ForegroundColor Cyan
 Write-Host ("Target: "+$PluginRoot)
 if(!(Test-Path $PluginRoot)){New-Item -ItemType Directory -Force -Path $PluginRoot|Out-Null}
 $m=[Text.Encoding]::UTF8.GetString((GetBytes ($ManifestUrl+"?t="+[DateTimeOffset]::UtcNow.ToUnixTimeSeconds())))|ConvertFrom-Json
 Write-Host ("Manifest: "+$m.version+" / "+$m.files.Count+" files")
 $changed=0
 foreach($f in $m.files){
  $rel=[string]$f.path;if(!$rel -or $rel.Contains("..")){throw "Unsafe path: $rel"}
  $target=Join-Path $PluginRoot ($rel -replace "/","\");$need=$true
  if(Test-Path -LiteralPath $target){if((BlobSha ([IO.File]::ReadAllBytes($target))) -eq ([string]$f.git_sha1).ToLower()){$need=$false}}
  if(!$need){continue}
  $sep=if(([string]$f.url).Contains("?")){"&"}else{"?"};$bytes=GetBytes (([string]$f.url)+$sep+"v="+$m.version)
  if((BlobSha $bytes) -ne ([string]$f.git_sha1).ToLower()){throw "SHA mismatch: $rel"}
  $dir=Split-Path -Parent $target;if(!(Test-Path $dir)){New-Item -ItemType Directory -Force -Path $dir|Out-Null}
  [IO.File]::WriteAllBytes($target+".hcnew",$bytes);Move-Item -LiteralPath ($target+".hcnew") -Destination $target -Force
  Write-Host ("[UPDATE] "+$rel) -ForegroundColor Yellow;$changed++
 }
 foreach($c in @("hcdecor-core.php","recovery-bootstrap.php","modules\background-sync.php","modules\social-manager.php")){if(!(Test-Path (Join-Path $PluginRoot $c))){throw "Missing critical file: $c"}}
 Write-Host ("[READY] "+$changed+" file(s) updated. Refresh WordPress.") -ForegroundColor Green
}
do{try{SyncHub}catch{Write-Host ("[ERROR] "+$_.Exception.Message) -ForegroundColor Red;if(!$Watch){exit 1}};if(!$Watch){break};Write-Host ("Next sync in "+$Interval+"s");Start-Sleep -Seconds ([Math]::Max(15,$Interval))}while($true)
