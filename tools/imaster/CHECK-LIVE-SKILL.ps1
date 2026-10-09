$ErrorActionPreference='Stop'
$h=Get-Content 'D:\HCDecorHUB\runtime\hcdr-relay-heartbeat.json' -Raw|ConvertFrom-Json
Write-Output ('PID='+$h.pid)
Write-Output ('HEARTBEAT_AT='+$h.at)
Write-Output ('SKILL_LOADED='+$h.skill_binding.loaded)
Write-Output ('SKILL_VALID='+$h.skill_binding.contract_valid)
Write-Output ('SKILL_SHA='+$h.skill_binding.sha256)
if($h.skill_binding.loaded -eq $true -and $h.skill_binding.contract_valid -eq $true){exit 0}
exit 22
