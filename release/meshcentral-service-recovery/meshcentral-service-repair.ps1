$ErrorActionPreference = 'Stop'
$root = 'D:\HCDecorHUB\TransportMesh'
$logDir = Join-Path $root 'logs'
New-Item -ItemType Directory -Path $logDir -Force | Out-Null
$log = Join-Path $logDir ('MeshCentral-Repair-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.log')
Start-Transcript -Path $log -Force | Out-Null
$exitCode = 1
try {
  $isAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
  if (-not $isAdmin) { throw 'ADMIN_REQUIRED' }
  $services = @(Get-CimInstance Win32_Service | Where-Object { $_.Name -ieq 'meshcentral.exe' -or $_.DisplayName -eq 'MeshCentral' })
  if ($services.Count -ne 1) { throw ('SERVICE_MATCH_COUNT=' + $services.Count) }
  $service = $services[0]
  if (([string]$service.PathName) -notmatch 'HCDecorHUB[\\/]MeshCentral[\\/]WinService[\\/]daemon[\\/]meshcentral\.exe') { throw 'UNEXPECTED_SERVICE_PATH' }
  Write-Output ('SERVICE=' + $service.Name + '; BEFORE=' + $service.State)
  & sc.exe config $service.Name start= delayed-auto
  if ($LASTEXITCODE -ne 0) { throw 'CONFIG_FAILED' }
  & sc.exe failure $service.Name reset= 86400 actions= restart/60000/restart/60000/restart/60000
  if ($LASTEXITCODE -ne 0) { throw 'RECOVERY_CONFIG_FAILED' }
  & sc.exe failureflag $service.Name 1
  if ($LASTEXITCODE -ne 0) { throw 'FAILURE_FLAG_FAILED' }
  if ((Get-Service -Name $service.Name).Status -ne 'Running') { Start-Service -Name $service.Name }
  Start-Sleep -Seconds 8
  $after = (Get-Service -Name $service.Name).Status
  & sc.exe qfailure $service.Name
  & sc.exe qc $service.Name
  Write-Output ('SERVICE_AFTER=' + $after)
  if ($after -ne 'Running') { throw 'SERVICE_NOT_RUNNING' }
  Write-Output 'SERVICE_RECOVERY_PASS; LONG_TERM_STABILITY_NOT_VERIFIED'
  $exitCode = 0
} catch {
  Write-Output ('SERVICE_REPAIR_ERROR=' + $_.Exception.Message)
} finally {
  Stop-Transcript | Out-Null
}
exit $exitCode
