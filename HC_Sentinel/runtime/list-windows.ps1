$ErrorActionPreference = "Stop"
Get-Process |
  Where-Object { $_.MainWindowTitle } |
  Select-Object @{n="id";e={$_.Id}}, @{n="processName";e={$_.ProcessName}}, @{n="hwnd";e={[int64]$_.MainWindowHandle}}, @{n="title";e={$_.MainWindowTitle}} |
  Sort-Object processName,title |
  ConvertTo-Json -Compress
