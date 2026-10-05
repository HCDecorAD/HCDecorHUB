$ErrorActionPreference='Stop'
Write-Output 'TUNNELCLIENT_FILES'
Get-ChildItem 'D:\HCDecorHUB\TunnelClient' -Recurse -File | ForEach-Object { $_.FullName }
Add-Type -AssemblyName System.IO.Compression.FileSystem
Get-ChildItem 'D:\HCDecorHUB\API KEY' -Filter '*.zip' -File | ForEach-Object {
 Write-Output ('ZIP='+$_.Name)
 $z=[IO.Compression.ZipFile]::OpenRead($_.FullName)
 try { $z.Entries | ForEach-Object { $_.FullName } } finally { $z.Dispose() }
}
