param(
  [ValidateSet("setup","build","sync","status")]
  [string]$Mode = "status"
)

$ErrorActionPreference = "Stop"
$RepoRoot = Split-Path -Parent $PSScriptRoot
$DataRoot = "D:\HC_DATA"
$RuntimeDir = Join-Path $RepoRoot ".runtime\local-first"

function Ensure-Dir([string]$Path) {
  if (-not (Test-Path $Path)) {
    New-Item -ItemType Directory -Force -Path $Path | Out-Null
  }
}

function Write-State([string]$Name, [hashtable]$Data) {
  Ensure-Dir $RuntimeDir
  $Data["timestamp"] = (Get-Date).ToString("o")
  $Data | ConvertTo-Json -Depth 8 | Set-Content -Encoding UTF8 (Join-Path $RuntimeDir "$Name.json")
}

function Setup-LocalFirst {
  $dirs = @(
    "$DataRoot\projects",
    "$DataRoot\builds",
    "$DataRoot\cache",
    "$DataRoot\artifacts",
    "$DataRoot\evidence",
    "$DataRoot\queue\pending",
    "$DataRoot\queue\running",
    "$DataRoot\queue\done",
    "$DataRoot\queue\failed",
    "$DataRoot\sync\github-pending",
    "$DataRoot\sync\github-synced",
    "$DataRoot\sync\retry"
  )
  $dirs | ForEach-Object { Ensure-Dir $_ }
  Ensure-Dir $RuntimeDir

  Write-State "setup" @{
    status = "PASS_DONE"
    computeProvider = "HOCUONG_LAPTOP"
    dataRoot = $DataRoot
    githubBlocking = $false
  }

  Write-Host "PASS DONE - Local-First setup ready"
  Write-Host "DATA: $DataRoot"
}

function Run-LocalBuild {
  Setup-LocalFirst
  Push-Location $RepoRoot
  try {
    $buildId = Get-Date -Format "yyyyMMdd-HHmmss"
    $buildDir = "$DataRoot\builds\HCDecorHUB\$buildId"
    Ensure-Dir $buildDir

    Write-State "build" @{
      status = "RUNNING"
      buildId = $buildId
      buildDir = $buildDir
    }

    if (-not (Test-Path (Join-Path $RepoRoot "node_modules"))) {
      Write-Host "[LOCAL] Installing dependencies..."
      npm ci
      if ($LASTEXITCODE -ne 0) { throw "npm ci failed ($LASTEXITCODE)" }
    }

    Write-Host "[LOCAL] Building HCDecorHUB..."
    npm run build
    if ($LASTEXITCODE -ne 0) { throw "npm run build failed ($LASTEXITCODE)" }

    @{
      buildId = $buildId
      repository = "HCDecorHUB"
      status = "PASS_DONE"
      machine = $env:COMPUTERNAME
      completedAt = (Get-Date).ToString("o")
    } | ConvertTo-Json | Set-Content -Encoding UTF8 (Join-Path $buildDir "build-result.json")

    Write-State "build" @{
      status = "PASS_DONE"
      buildId = $buildId
      buildDir = $buildDir
      githubSync = "NOT_REQUIRED_FOR_DONE"
    }
    Write-Host "PASS DONE - LOCAL BUILD"
  }
  catch {
    Write-State "build" @{
      status = "FAILED"
      error = $_.Exception.Message
    }
    throw
  }
  finally {
    Pop-Location
  }
}

function Sync-GitHub {
  Setup-LocalFirst
  Push-Location $RepoRoot
  try {
    $dirty = git status --porcelain
    if ($LASTEXITCODE -ne 0) { throw "git status failed" }

    if (-not $dirty) {
      Write-State "github-sync" @{ status = "PASS_DONE"; result = "NOTHING_TO_SYNC" }
      Write-Host "PASS DONE - Nothing to sync"
      return
    }

    git add -A
    if ($LASTEXITCODE -ne 0) { throw "git add failed" }

    $msg = "local-sync: HOCUONG $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    git commit -m $msg
    if (($LASTEXITCODE -ne 0) -and ($LASTEXITCODE -ne 1)) { throw "git commit failed" }

    git push
    if ($LASTEXITCODE -ne 0) { throw "git push failed ($LASTEXITCODE)" }

    $marker = "$DataRoot\sync\github-synced\$(Get-Date -Format 'yyyyMMdd-HHmmss').json"
    @{ status="SYNCED"; commit=(git rev-parse HEAD); at=(Get-Date).ToString("o") } |
      ConvertTo-Json | Set-Content -Encoding UTF8 $marker

    Write-State "github-sync" @{ status = "PASS_DONE"; result = "SYNCED" }
    Write-Host "PASS DONE - GitHub synced"
  }
  catch {
    $pending = "$DataRoot\sync\github-pending\$(Get-Date -Format 'yyyyMMdd-HHmmss').json"
    @{
      status = "PENDING"
      reason = $_.Exception.Message
      repo = $RepoRoot
      at = (Get-Date).ToString("o")
    } | ConvertTo-Json | Set-Content -Encoding UTF8 $pending

    Write-State "github-sync" @{
      status = "PENDING"
      reason = $_.Exception.Message
      blocking = $false
    }
    Write-Warning "GitHub sync PENDING - local DONE remains valid."
    exit 0
  }
  finally {
    Pop-Location
  }
}

function Show-Status {
  Setup-LocalFirst
  Write-Host ""
  Write-Host "=== HC LOCAL-FIRST STATUS ==="
  Write-Host "Compute : HOCUONG_LAPTOP"
  Write-Host "DATA    : $DataRoot"
  Write-Host "Build   : LOCAL"
  Write-Host "GitHub  : ASYNC / NON-BLOCKING"
  $pendingCount = @(Get-ChildItem "$DataRoot\sync\github-pending" -File -ErrorAction SilentlyContinue).Count
  Write-Host "Pending : $pendingCount"
}

switch ($Mode) {
  "setup"  { Setup-LocalFirst }
  "build"  { Run-LocalBuild }
  "sync"   { Sync-GitHub }
  "status" { Show-Status }
}
