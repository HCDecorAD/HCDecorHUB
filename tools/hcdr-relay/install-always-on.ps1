param(
  [string]$TaskName='HCDecor-HCDR-AlwaysOn',
  [string]$WatchdogTaskName='HCDecor-HCDR-Watchdog'
)
$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\repos\HCDecorHUB'
$launcher=Join-Path $root 'tools\hcdr-relay\HCDR-Remote-Free.cmd'
$watchdog=Join-Path $root 'tools\hcdr-relay\watchdog.ps1'
if(!(Test-Path $launcher)){throw 'launcher_missing'}
if(!(Test-Path $watchdog)){throw 'watchdog_missing'}

$who=[System.Security.Principal.WindowsIdentity]::GetCurrent().Name
$action=New-ScheduledTaskAction -Execute 'cmd.exe' -Argument ('/d /s /c "'+$launcher+'"')
$boot=New-ScheduledTaskTrigger -AtStartup
$login=New-ScheduledTaskTrigger -AtLogOn
$settings=New-ScheduledTaskSettingsSet -StartWhenAvailable -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -RestartCount 999 -RestartInterval (New-TimeSpan -Minutes 1) -ExecutionTimeLimit ([TimeSpan]::Zero)
try{
  Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger @($boot,$login) -Settings $settings -User 'SYSTEM' -RunLevel Highest -Force | Out-Null
}catch{
  Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $login -Settings $settings -User $who -RunLevel Highest -Force | Out-Null
}

$watchAction=New-ScheduledTaskAction -Execute 'powershell.exe' -Argument ('-NoProfile -ExecutionPolicy Bypass -File "'+$watchdog+'"')
$watchTrigger=New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes 1)
try{
  Register-ScheduledTask -TaskName $WatchdogTaskName -Action $watchAction -Trigger $watchTrigger -Settings $settings -User 'SYSTEM' -RunLevel Highest -Force | Out-Null
}catch{
  Register-ScheduledTask -TaskName $WatchdogTaskName -Action $watchAction -Trigger $watchTrigger -Settings $settings -User $who -RunLevel Highest -Force | Out-Null
}

Start-ScheduledTask -TaskName $TaskName
Start-Sleep -Seconds 12
& powershell.exe -NoProfile -ExecutionPolicy Bypass -File $watchdog
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
Write-Output ('HCDR_ALWAYS_ON_INSTALLED main='+$TaskName+' watchdog='+$WatchdogTaskName)
