@echo off
setlocal
cd /d %~dp0..
for %%D in (data logs checkpoints dist runtime backups) do if not exist "%%D" mkdir "%%D"
if not exist data\chats.json echo {"version":1,"chats":{}}>data\chats.json
if not exist data\queue.json echo {"items":[],"global_paused":false,"paused_aliases":[]}>data\queue.json
echo BOOTSTRAP_PASS
exit /b 0
