param([ValidateSet('meshcentral_status','meshcentral_logs')][string]$Task='meshcentral_status')
$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\TransportMesh'
$result=[ordered]@{task=$Task;timestamp=(Get-Date).ToString('o');status='ERROR';data=$null;error=$null}
try {
 if($Task -eq 'meshcentral_status'){
  $svc=Get-CimInstance Win32_Service -Filter "Name='meshcentral.exe'"
  if(!$svc){throw 'SERVICE_NOT_FOUND'}
  $result.data=@{name=$svc.Name;state=$svc.State;start_mode=$svc.StartMode}
 }else{
  $dir=Join-Path $root 'logs'
  $result.data=@(Get-ChildItem -LiteralPath $dir -File -ErrorAction SilentlyContinue | Where-Object {$_.Name -like 'Mesh-Admin-Repair-*'} | Sort-Object LastWriteTime -Descending | Select-Object -First 10 Name,Length,LastWriteTime)
 }
 $result.status='PASS'
}catch{$result.error=$_.Exception.Message}
$result | ConvertTo-Json -Depth 6 | Set-Content (Join-Path $root 'MESH-TASK-READONLY-RESULT.json') -Encoding UTF8
Write-Output ('TASK='+$Task+' STATUS='+$result.status)
if($result.status -ne 'PASS'){exit 1}
