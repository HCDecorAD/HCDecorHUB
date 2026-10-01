param([string]$Root=(Split-Path -Parent $PSScriptRoot))
$ErrorActionPreference='Stop';Set-Location $Root
$sha=(git rev-parse HEAD).Trim();if($LASTEXITCODE){throw 'git_head_failed'}
$dirty=(git status --porcelain);if($dirty){throw 'working_tree_not_clean'}
$required=@('hcdecor-complete.bat','hcdecor-foundation-full.bat','hcdecor-final-e2e.bat','hcdecor-release-ready.bat','hcdecor-release-certify.bat','hcdecor-release-full.bat')
$missing=@($required|?{!(Test-Path (Join-Path $Root $_))});if($missing.Count){throw ('missing_release_tools:'+($missing -join ','))}
$record=[ordered]@{schema='hcdecor-release-lock/v1';source_sha=$sha;source_authority='github-main';production_mutation='locked';local_runtime_authority=$false;required_tools=$required;generated_at=(Get-Date).ToUniversalTime().ToString('o')}
$out=Join-Path $Root '.runtime\release-lock';New-Item -ItemType Directory -Force $out|Out-Null
$record|ConvertTo-Json -Depth 5|Set-Content (Join-Path $out 'LOCK.json')
Write-Output "HCDECOR_RELEASE_LOCK_READY $sha"
