$ErrorActionPreference = "SilentlyContinue"
Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

$Root = Split-Path -Parent $PSScriptRoot
$Queue = Join-Path $Root "runtime-state\tray-notifications.jsonl"
$notify = New-Object System.Windows.Forms.NotifyIcon
$notify.Icon = [System.Drawing.SystemIcons]::Application
$notify.Text = "HC Sentinel"
$notify.Visible = $true

$menu = New-Object System.Windows.Forms.ContextMenuStrip
$open = $menu.Items.Add("Open Command Center")
$open.Add_Click({ Start-Process "http://127.0.0.1:43110" })
$exit = $menu.Items.Add("Exit Tray")
$exit.Add_Click({ $notify.Visible = $false; [System.Windows.Forms.Application]::Exit() })
$notify.ContextMenuStrip = $menu
$notify.Add_DoubleClick({ Start-Process "http://127.0.0.1:43110" })
$notify.ShowBalloonTip(1500, "HC Sentinel", "Sentinel is running.", [System.Windows.Forms.ToolTipIcon]::Info)

$seen = 0
$timer = New-Object System.Windows.Forms.Timer
$timer.Interval = 1500
$timer.Add_Tick({
  if(Test-Path $Queue){
    $lines = @(Get-Content $Queue)
    if($lines.Count -gt $seen){
      for($i=$seen; $i -lt $lines.Count; $i++){
        try{
          $x = $lines[$i] | ConvertFrom-Json
          $icon = if($x.level -eq "error") {[System.Windows.Forms.ToolTipIcon]::Error} elseif($x.level -eq "warning") {[System.Windows.Forms.ToolTipIcon]::Warning} else {[System.Windows.Forms.ToolTipIcon]::Info}
          $notify.ShowBalloonTip(3000, [string]$x.title, [string]$x.message, $icon)
        }catch{}
      }
      $seen = $lines.Count
    }
  }
})
$timer.Start()
[System.Windows.Forms.Application]::Run()
