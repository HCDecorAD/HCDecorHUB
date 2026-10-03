param(
  [string]$RelayRepo='HCDecorAD/HCDecor-HCDR-Relay',
  [string]$RemotePath='observability/hc-mat-than/latest.png'
)
$ErrorActionPreference='Stop'
$shot='D:\HCDecorHUB\runtime\hc-mat-than\latest.png'
if(!(Test-Path $shot)){throw 'hc_mat_than_screenshot_missing'}

$bytes=[IO.File]::ReadAllBytes($shot)
$content=[Convert]::ToBase64String($bytes)
$sha=$null
try{
  $existing=gh api ('repos/'+$RelayRepo+'/contents/'+$RemotePath) 2>$null | ConvertFrom-Json
  $sha=$existing.sha
}catch{}

$tmp=Join-Path $env:TEMP ('hc-mat-than-upload-'+[guid]::NewGuid().ToString('N')+'.json')
$body=[ordered]@{
  message='obs: refresh HC Mat Than latest screenshot'
  content=$content
}
if($sha){$body.sha=$sha}
$body|ConvertTo-Json -Depth 4|Set-Content -Encoding UTF8 $tmp
try{
  $result=Get-Content $tmp -Raw | gh api ('repos/'+$RelayRepo+'/contents/'+$RemotePath) --method PUT --input -
  if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
  $j=$result|ConvertFrom-Json
  Write-Output ('HC_MAT_THAN_VISION_EXPORT_PASS path='+$RemotePath+' sha='+$j.content.sha)
}finally{
  Remove-Item -Force $tmp -ErrorAction SilentlyContinue
}
