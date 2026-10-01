param([string]$Source,[string]$Output,[int]$MaxWidth=2400,[int]$JpegQuality=86)
$ErrorActionPreference='Stop'
if(!(Test-Path $Source)){throw "Source not found: $Source"}
Add-Type -AssemblyName System.Drawing
$img=[System.Drawing.Image]::FromFile($Source)
try{
  $ratio=[Math]::Min(1.0,$MaxWidth/[double]$img.Width)
  $w=[int][Math]::Round($img.Width*$ratio); $h=[int][Math]::Round($img.Height*$ratio)
  $bmp=New-Object System.Drawing.Bitmap($w,$h)
  try{
    $g=[System.Drawing.Graphics]::FromImage($bmp)
    try{$g.InterpolationMode=[System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic;$g.DrawImage($img,0,0,$w,$h)}
    finally{$g.Dispose()}
    $codec=[System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders()|Where-Object {$_.MimeType -eq 'image/jpeg'}|Select-Object -First 1
    $ep=New-Object System.Drawing.Imaging.EncoderParameters(1);$ep.Param[0]=New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality,[long]$JpegQuality)
    $dir=Split-Path $Output -Parent;if($dir -and !(Test-Path $dir)){New-Item -ItemType Directory -Force -Path $dir|Out-Null}
    $bmp.Save($Output,$codec,$ep)
  }finally{$bmp.Dispose()}
  $o=Get-Item $Output
  [pscustomobject]@{Source=$Source;SourceWidth=$img.Width;SourceHeight=$img.Height;Output=$o.FullName;OutputWidth=$w;OutputHeight=$h;Bytes=$o.Length}|ConvertTo-Json
}finally{$img.Dispose()}
