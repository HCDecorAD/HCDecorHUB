$ErrorActionPreference = "Stop"
$repo = "D:\HCDecorHUB\repos\HCDecorHUB"
$ruleRoot = "D:\HCDecorHUB\Rule"
New-Item -ItemType Directory -Force -Path $ruleRoot | Out-Null
Copy-Item (Join-Path $repo "Rule\NO-COMPLEX-INLINE-POWERSHELL.md") (Join-Path $ruleRoot "NO-COMPLEX-INLINE-POWERSHELL.md") -Force
$target = Join-Path $ruleRoot "NO-COMPLEX-INLINE-POWERSHELL.md"
if (!(Test-Path $target)) { throw "RULE_COPY_FAILED" }
Write-Output "RULE_HARD_GLOBAL_PASS $target"
