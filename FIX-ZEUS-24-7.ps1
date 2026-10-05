param([string]$RepoRoot="D:\HCDecorHUB\repos\HCDecorHUB")
$ErrorActionPreference="Stop"
$log="D:\HCDecorHUB\Zeus247\FIX-24-7.log"
New-Item -ItemType Directory -Force (Split-Path $log)|Out-Null
function S($x){("["+(Get-Date -Format s)+"] "+$x)|Tee-Object -FilePath $log -Append}
S "FIX_24_7_START"
Push-Location $RepoRoot
try{
 git fetch origin main; if($LASTEXITCODE){throw "FETCH_FAIL"}
 git checkout main; if($LASTEXITCODE){throw "CHECKOUT_FAIL"}
 git pull --ff-only origin main; if($LASTEXITCODE){throw "PULL_FAIL"}
 S ("SOURCE="+(git rev-parse HEAD))
 & (Join-Path $RepoRoot "tools\transwarp-local\install-transwarp-local.ps1") -RepoRoot $RepoRoot
 if($LASTEXITCODE){throw "INSTALL_FAIL"}
 Start-Sleep -Seconds 3
 $h=Invoke-RestMethod "http://127.0.0.1:8766/health" -TimeoutSec 5
 if(-not $h.ok){throw "BRIDGE_HEALTH_FAIL"}
 $t=(Invoke-RestMethod "http://127.0.0.1:8766/tabs" -TimeoutSec 10).tabs
 $expected=@("Zeus 24/7","HCDecorHUB V10","Update PASS","Tiếp tục PANDA LIVE")
 $fleet=@()
 foreach($n in $expected){$m=@($t|Where-Object{[string]$_.title -eq $n});$fleet+=[ordered]@{title=$n;matches=$m.Count;cid=if($m.Count-eq 1){$m[0].cid}else{$null};busy=if($m.Count-eq 1){$m[0].busy}else{$null}}}
 $ev=[ordered]@{schema="zeus247/fix-package-v1";at=(Get-Date).ToString("o");source=(git rev-parse HEAD);health=$h;tabs_seen=@($t).Count;fleet=$fleet}
 $ev|ConvertTo-Json -Depth 8|Set-Content "D:\HCDecorHUB\Zeus247\FIX-24-7-EVIDENCE.json" -Encoding UTF8
 $bad=@($fleet|Where-Object{$_.matches-ne 1})
 if($bad.Count){S ("FIX_24_7_RUNTIME_RECONCILE_REQUIRED="+(($bad.title)-join ","));exit 2}
 S "FIX_24_7_PACKAGE_PASS"
 Write-Output "FIX_24_7_PACKAGE_PASS"
}finally{Pop-Location}
