param(
  [int]$IntervalSeconds=15,
  [switch]$Once
)
$ErrorActionPreference='Continue'
$root='D:\HCDecorHUB'
$out=Join-Path $root 'runtime\hc-mat-than'
New-Item -ItemType Directory -Force -Path $out | Out-Null
$stateFile=Join-Path $out 'state.json'
$shotFile=Join-Path $out 'latest.png'
$history=Join-Path $out 'history'
New-Item -ItemType Directory -Force -Path $history | Out-Null

Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing
Add-Type @"
using System;
using System.Runtime.InteropServices;
using System.Text;
public static class HCWindow {
  [DllImport("user32.dll")] public static extern IntPtr GetForegroundWindow();
  [DllImport("user32.dll", CharSet=CharSet.Unicode)] public static extern int GetWindowText(IntPtr hWnd, StringBuilder text, int count);
  public static string Title() {
    var h=GetForegroundWindow();
    var sb=new StringBuilder(1024);
    GetWindowText(h,sb,sb.Capacity);
    return sb.ToString();
  }
}
"@

function Capture-Screen {
  try {
    $bounds=[System.Windows.Forms.SystemInformation]::VirtualScreen
    $bmp=New-Object System.Drawing.Bitmap $bounds.Width,$bounds.Height
    $g=[System.Drawing.Graphics]::FromImage($bmp)
    $g.CopyFromScreen($bounds.Left,$bounds.Top,0,0,$bounds.Size)
    $bmp.Save($shotFile,[System.Drawing.Imaging.ImageFormat]::Png)
    $stamp=(Get-Date).ToString('yyyyMMdd-HHmmss')
    Copy-Item -Force $shotFile (Join-Path $history ($stamp+'.png'))
    $g.Dispose();$bmp.Dispose()
    Get-ChildItem $history -Filter *.png | Sort-Object LastWriteTime -Descending | Select-Object -Skip 40 | Remove-Item -Force -ErrorAction SilentlyContinue
    return $true
  } catch { return $false }
}

function Snapshot {
  $now=(Get-Date).ToUniversalTime()
  $title=[HCWindow]::Title()
  $shot=Capture-Screen
  $hbPath=Join-Path $root 'runtime\hcdr-relay-heartbeat.json'
  $hb=$null
  if(Test-Path $hbPath){try{$hb=Get-Content $hbPath -Raw|ConvertFrom-Json}catch{}}
  $nodes=@(Get-Process node -ErrorAction SilentlyContinue | Select-Object Id,CPU,StartTime)
  $powershell=@(Get-Process powershell -ErrorAction SilentlyContinue | Select-Object Id,CPU,StartTime)
  $classification='ACTIVE'
  if([string]::IsNullOrWhiteSpace($title)){$classification='IDLE'}
  if($hb -and $hb.worker_pool -and [int]$hb.worker_pool.active -gt 0){$classification='BUSY'}
  $state=[ordered]@{
    schema='hc-mat-than/state-v1'
    at=$now.ToString('o')
    observer_pid=$PID
    active_window_title=$title
    screenshot_ok=$shot
    screenshot_path=$shotFile
    classification=$classification
    hcdr=$hb
    processes=[ordered]@{node=$nodes;powershell=$powershell}
  }
  $state|ConvertTo-Json -Depth 10|Set-Content -Encoding UTF8 $stateFile
  Write-Output ('HC_MAT_THAN '+$classification+' '+$title)
}

do {
  Snapshot
  if($Once){break}
  Start-Sleep -Seconds ([Math]::Max(5,$IntervalSeconds))
} while($true)
