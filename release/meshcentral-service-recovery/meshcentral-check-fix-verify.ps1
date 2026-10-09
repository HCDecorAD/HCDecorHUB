$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\TransportMesh'
$checkpoint=Join-Path $root 'MESH-SERVICE-REPAIR-RESULT.json'
$result=[ordered]@{schema='imaster/mesh-service-repair-result/v1';timestamp=(Get-Date).ToString('o');repair_exit_code=$null;service_name=$null;service_status='UNKNOWN';startup_mode='UNKNOWN';recovery='NOT_VERIFIED';gateway='NOT_CHECKED';public='NOT_VERIFIED';error=$null}
try {
 $admin=([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
 if(-not $admin){throw 'ADMIN_REQUIRED'}
 & powershell.exe -NoProfile -ExecutionPolicy Bypass -File (Join-Path $root 'meshcentral-service-repair.ps1')
 $result.repair_exit_code=$LASTEXITCODE
 $svcs=@(Get-CimInstance Win32_Service | Where-Object {$_.Name -ieq 'meshcentral.exe' -or $_.DisplayName -eq 'MeshCentral'})
 if($svcs.Count -ne 1){throw ('SERVICE_COUNT='+$svcs.Count)}
 $s=$svcs[0]
 $result.service_name=$s.Name
 $result.service_status=$s.State
 $result.startup_mode=$s.StartMode
 $recovery=(& sc.exe qfailure $s.Name | Out-String)
 $result.recovery=if($LASTEXITCODE -eq 0 -and $recovery -match 'RESTART'){ 'RESTART_CONFIGURED' }else{'CHECK_REQUIRED'}
 if($result.repair_exit_code -ne 0 -or $s.State -ne 'Running'){throw 'SERVICE_REPAIR_OR_RUNNING_CHECK_FAILED'}
} catch { $result.error=$_.Exception.Message }
finally {
 $result.timestamp=(Get-Date).ToString('o')
 $result | ConvertTo-Json -Depth 5 | Set-Content -LiteralPath $checkpoint -Encoding UTF8
 Write-Output ('CHECKPOINT='+$checkpoint)
 Write-Output ('SERVICE='+$result.service_status+'; RECOVERY='+$result.recovery+'; ERROR='+$result.error)
}
if($result.error){exit 1}
