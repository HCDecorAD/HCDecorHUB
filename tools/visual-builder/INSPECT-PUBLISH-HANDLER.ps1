$ErrorActionPreference='Stop'
$p='D:\HCDecorHUB\HC_Visual_Builder\src\App.tsx'
$s=Get-Content $p -Raw -Encoding UTF8
foreach($term in @('publish','localStorage.setItem','wp-json','PKEY','onClick={publish}')) {
 $idx=0;$count=0
 while(($idx=$s.IndexOf($term,$idx,[StringComparison]::OrdinalIgnoreCase)) -ge 0 -and $count -lt 5) {
  $start=[Math]::Max(0,$idx-350);$len=[Math]::Min(1000,$s.Length-$start)
  Write-Output ('TERM='+$term+' INDEX='+$idx)
  Write-Output ($s.Substring($start,$len))
  $idx+=$term.Length;$count++
 }
}
