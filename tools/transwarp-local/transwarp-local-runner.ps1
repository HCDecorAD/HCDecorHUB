param([string]$DataRoot="D:\HC_DATA\queue\transwarp")
$ErrorActionPreference="Stop"
$mutex=New-Object System.Threading.Mutex($false,"Global\HC_TransWarp_Local_Runner")
if(-not $mutex.WaitOne(0,$false)){Write-Output "TRANSWARP_LOCAL_ALREADY_RUNNING";exit 0}
$Inbox=Join-Path $DataRoot "inbox"; $Running=Join-Path $DataRoot "running"; $Done=Join-Path $DataRoot "done"; $Failed=Join-Path $DataRoot "failed"; $Logs=Join-Path $DataRoot "logs"; $Evidence=Join-Path $DataRoot "evidence"
@($Inbox,$Running,$Done,$Failed,$Logs,$Evidence)|ForEach-Object{New-Item -ItemType Directory -Force -Path $_|Out-Null}
$allowedRoots=@("D:\HCDecorHUB","D:\HC_DATA")
$allowedExe=@("node","node.exe","npm","npm.cmd","npx","npx.cmd","git","git.exe","python","python.exe","py","py.exe","powershell","powershell.exe","pwsh","pwsh.exe")
function Test-AllowedPath([string]$p){if([string]::IsNullOrWhiteSpace($p)){return $false};$full=[IO.Path]::GetFullPath($p);foreach($r in $allowedRoots){if($full.StartsWith($r,[StringComparison]::OrdinalIgnoreCase)){return $true}};return $false}
function Write-Evidence($job,$state,$code,$log,$err){$o=[ordered]@{schema="transwarp-local/evidence-v1";job_id=$job.job_id;state=$state;exit_code=$code;correlation_id=$job.correlation_id;cwd=$job.cwd;action=$job.action;finished_at=(Get-Date).ToString("o");log=$log;error=$err};$o|ConvertTo-Json -Depth 8|Set-Content (Join-Path $Evidence ($job.job_id+".json")) -Encoding UTF8}
Write-Output ("TRANSWARP_LOCAL_RUNNER_READY root="+$DataRoot)
try{
while($true){
  $jobs=Get-ChildItem $Inbox -Filter *.json -File -ErrorAction SilentlyContinue|Sort-Object CreationTime
  foreach($f in $jobs){
    $runFile=Join-Path $Running $f.Name
    try{
      Move-Item $f.FullName $runFile -Force
      $job=Get-Content $runFile -Raw|ConvertFrom-Json
      if($job.schema -ne "transwarp-local/job-v1"){throw "SCHEMA_INVALID"}
      if($job.approved -ne $true){throw "JOB_NOT_APPROVED"}
      if(-not (Test-AllowedPath $job.cwd)){throw "CWD_NOT_ALLOWED"}
      $log=Join-Path $Logs ($job.job_id+".log");$err=Join-Path $Logs ($job.job_id+".err.log");$code=1
      if($job.action -eq "process"){
        if($allowedExe -notcontains [string]$job.executable){throw "EXECUTABLE_NOT_ALLOWED"}
        $args=@();if($job.arguments){$args=@($job.arguments|ForEach-Object{[string]$_})}
        $p=Start-Process -FilePath $job.executable -ArgumentList $args -WorkingDirectory $job.cwd -NoNewWindow -Wait -PassThru -RedirectStandardOutput $log -RedirectStandardError $err;$code=$p.ExitCode
      }elseif($job.action -eq "powershell_file"){
        if(-not (Test-AllowedPath $job.script)){throw "SCRIPT_NOT_ALLOWED"}
        $args=@("-NoProfile","-ExecutionPolicy","Bypass","-File",[string]$job.script);if($job.arguments){$args+=@($job.arguments|ForEach-Object{[string]$_})}
        $p=Start-Process -FilePath "powershell.exe" -ArgumentList $args -WorkingDirectory $job.cwd -NoNewWindow -Wait -PassThru -RedirectStandardOutput $log -RedirectStandardError $err;$code=$p.ExitCode
      }else{throw "ACTION_NOT_ALLOWED"}
      if($code -eq 0){Write-Evidence $job "DONE" $code $log $null;Move-Item $runFile (Join-Path $Done $f.Name) -Force}else{Write-Evidence $job "FAILED" $code $log (Get-Content $err -Raw -ErrorAction SilentlyContinue);Move-Item $runFile (Join-Path $Failed $f.Name) -Force}
    }catch{
      try{if(Test-Path $runFile){$job=Get-Content $runFile -Raw|ConvertFrom-Json -ErrorAction SilentlyContinue;if($job){Write-Evidence $job "FAILED" 1 $null $_.Exception.Message};Move-Item $runFile (Join-Path $Failed $f.Name) -Force}}catch{}
    }
  }
  if($Once){break}
  Start-Sleep -Milliseconds 750
}
}finally{$mutex.ReleaseMutex()|Out-Null;$mutex.Dispose()}