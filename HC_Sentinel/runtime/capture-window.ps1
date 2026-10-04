param([Parameter(Mandatory=$true)][Int64]$Hwnd,[Parameter(Mandatory=$true)][string]$Output)
$ErrorActionPreference = "Stop"
Add-Type -AssemblyName System.Drawing
Add-Type @"
using System;
using System.Runtime.InteropServices;
public static class HCWinCap {
 [StructLayout(LayoutKind.Sequential)] public struct RECT { public int Left; public int Top; public int Right; public int Bottom; }
 [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr hWnd, out RECT rect);
 [DllImport("user32.dll")] public static extern bool PrintWindow(IntPtr hWnd, IntPtr hdcBlt, uint flags);
}
"@
$rect = New-Object HCWinCap+RECT
if(-not [HCWinCap]::GetWindowRect([IntPtr]$Hwnd,[ref]$rect)){ throw "WINDOW_RECT_FAILED" }
$w=$rect.Right-$rect.Left; $h=$rect.Bottom-$rect.Top
if($w -lt 2 -or $h -lt 2){ throw "WINDOW_NOT_CAPTURABLE" }
$bmp=New-Object System.Drawing.Bitmap $w,$h
$g=[System.Drawing.Graphics]::FromImage($bmp)
$hdc=$g.GetHdc()
$ok=[HCWinCap]::PrintWindow([IntPtr]$Hwnd,$hdc,2)
$g.ReleaseHdc($hdc)
if(-not $ok){
  $g.CopyFromScreen($rect.Left,$rect.Top,0,0,$bmp.Size)
}
$dir=Split-Path -Parent $Output
if($dir){New-Item -ItemType Directory -Force -Path $dir|Out-Null}
$bmp.Save($Output,[System.Drawing.Imaging.ImageFormat]::Png)
$g.Dispose();$bmp.Dispose()
$hash=(Get-FileHash -Algorithm SHA256 -Path $Output).Hash.ToLowerInvariant()
@{hash=$hash;path=$Output;width=$w;height=$h}|ConvertTo-Json -Compress
