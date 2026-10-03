param(
  [string]$TaskName='HCDecor-HCDR-AlwaysOn',
  [string]$WatchdogTaskName='HCDecor-HCDR-Watchdog'
)
$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\repos\HCDecorHUB'
$launcher=Join-Path $root 'tools\hcdr-relay\HCDR-Remote-Free.cmd'
$watchdog=Join-Path $root 'tools\hcdr-relay\watchdog.ps1'
$userSupervisor=Join-Path $root 'tools\hcdr-relay\user-supervisor.ps1'
if(!(Test-Path $launcher)){throw 'launcher_missing'}
if(!(Test-Path $watchdog)){throw 'watchdog_missing'}
if(!(Test-Path $userSupervisor)){throw 'user_supervisor_missing'}

# Retire known legacy auto-start hooks so only canonical v3 owns hcdr-job.
$startup=[Environment]::GetFolderPath('Startup')
$legacyStartup=Join-Path $startup 'HCDecor-HCDR-Supervisor.bat'
if(Test-Path $legacyStartup){
  Rename-Item -Path $legacyStartup -NewName 'HCDecor-HCDR-Supervisor.bat.disabled' -Force -ErrorAction SilentlyContinue
}
try{
  $runKey='HKCU\Software\Microsoft\Windows\CurrentVersion\Run'
  cmd /d /c "reg query \"$runKey\" /v HCDRV11Relay >nul 2>&1"
  if($LASTEXITCODE -eq 0){cmd /d /c "reg delete \"$runKey\" /v HCDRV11Relay /f" | Out-Null}
}catch{}

$who=[System.Security.Principal.WindowsIdentity]::GetCurrent().Name
$settings=New-ScheduledTaskSettingsSet -StartWhenAvailable -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -RestartCount 999 -RestartInterval (New-TimeSpan -Minutes 1) -ExecutionTimeLimit ([TimeSpan]::Zero)
$scheduled=$false
try{
  $action=New-ScheduledTaskAction -Execute 'cmd.exe' -Argument ('/d /s /c "'+$launcher+'"')
  $boot=New-ScheduledTaskTrigger -AtStartup
  $login=New-ScheduledTaskTrigger -AtLogOn
  try{
    Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger @($boot,$login) -Settings $settings -User 'SYSTEM' -RunLevel Highest -Force | Out-Null
  }catch{
    Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $login -Settings $settings -User $who -Force | Out-Null
  }

  $watchAction=New-ScheduledTaskAction -Execute 'powershell.exe' -Argument ('-NoProfile -ExecutionPolicy Bypass -File "'+$watchdog+'"')
  $watchTrigger=New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes 1)
  try{
    Register-ScheduledTask -TaskName $WatchdogTaskName -Action $watchAction -Trigger $watchTrigger -Settings $settings -User 'SYSTEM' -RunLevel Highest -Force | Out-Null
  }catch{
    Register-ScheduledTask -TaskName $WatchdogTaskName -Action $watchAction -Trigger $watchTrigger -Settings $settings -User $who -Force | Out-Null
  }
  Start-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue
  $scheduled=$true
}catch{
  $scheduled=$false
}

if(-not $scheduled){
  $startupCmd=Join-Path $startup 'HCDecor-HCDR-V3.cmd'
  $cmdLines=@(
    '@echo off',
    'start "" /min powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File "'+$userSupervisor+'"'
  )
  Set-Content -Path $startupCmd -Value $cmdLines -Encoding ASCII
  Start-Process -FilePath 'powershell.exe' -ArgumentList '-NoProfile','-WindowStyle','Hidden','-ExecutionPolicy','Bypass','-File',('"'+$userSupervisor+'"') -WindowStyle Hidden
  Start-Sleep -Seconds 2
  Write-Output ('HCDR_ALWAYS_ON_FALLBACK_STARTUP path='+$startupCmd)
}

& powershell.exe -NoProfile -ExecutionPolicy Bypass -File $watchdog
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
$mode=if($scheduled){'scheduled-task'}else{'startup-supervisor'}
Write-Output ('HCDR_ALWAYS_ON_INSTALLED mode='+$mode+' main='+$TaskName+' watchdog='+$WatchdogTaskName)
