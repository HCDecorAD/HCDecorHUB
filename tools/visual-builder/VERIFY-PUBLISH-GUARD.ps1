$ErrorActionPreference='Stop'
$root='D:\HCDecorHUB\HC_Visual_Builder'
$src=Join-Path $root 'src\App.tsx'
$s=[IO.File]::ReadAllText($src)
$checks=[ordered]@{
  handler_guard=$s.Contains('PUBLISH BLOCKED: authenticated WordPress write')
  preview_only=$s.Contains('LOCAL PREVIEW ONLY - NOT PUBLIC')
  fake_success_absent=(-not $s.Contains("alert(kind.toUpperCase()+' PASS')"))
  wordpress_read_binding=$s.Contains("https://hcdecorhub.com/wp-json")
  build_output=(Test-Path (Join-Path $root 'dist\index.html'))
  backup=(Test-Path ($src+'.pre-real-publish-guard.bak'))
}
$checks.GetEnumerator()|ForEach-Object { Write-Output ("{0}={1}" -f $_.Key,$_.Value) }
if($checks.Values -contains $false){exit 2}
Write-Output 'PUBLISH_GUARD_CHECK=PASS'
