@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HCDecor HUB Production Operator
set "ROOT=%~dp0"
cd /d "%ROOT%"
echo [1/7] Repository
where git >nul 2>nul || (echo ERROR: git not found.& exit /b 10)
git rev-parse --is-inside-work-tree >nul 2>nul || (echo ERROR: run inside HCDecorHUB repo.& exit /b 11)
git status --short
echo [2/7] Manifest
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference='Stop'; $m=Get-Content -Raw 'wordpress/hcdecor-sync-manifest.json'|ConvertFrom-Json; if(-not $m.version -or -not $m.files){throw 'Invalid sync manifest'}; Write-Host ('Manifest '+$m.version+' / '+$m.files.Count+' files')" || exit /b 20
echo [3/7] Integrity
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference='Stop'; $m=Get-Content -Raw 'wordpress/hcdecor-sync-manifest.json'|ConvertFrom-Json; foreach($f in $m.files){$p=Join-Path 'wordpress\hcdecor-core' $f.path; if(-not(Test-Path -LiteralPath $p)){throw ('Missing: '+$f.path)}; $actual=(& git hash-object -- $p).Trim(); if($actual -ne $f.git_sha1){throw ('SHA mismatch: '+$f.path)}}; Write-Host ('Integrity OK / '+$m.files.Count+' files')" || exit /b 25
echo [4/7] PHP syntax
where php >nul 2>nul
if errorlevel 1 (echo SKIP: php CLI not installed.) else (for /r "wordpress\hcdecor-core" %%F in (*.php) do (php -l "%%F" >nul || exit /b 30))
echo [5/7] Production guards
findstr /c:"'social_enabled'=>false" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 40
findstr /c:"'hc_outbound',false" "wordpress\hcdecor-core\modules\agent-intake.php" >nul || exit /b 41
findstr /c:"Reviewer audit is required before publish." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 42
findstr /c:"hc_publish_approved_at" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 43
findstr /c:"Explicit production rollback approval is required." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 61
findstr /c:"Web rollback requires explicit admin approval in WordPress." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 62
findstr /c:"production_approved" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 44
findstr /c:"Production approval required" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 46
findstr /c:"HCDECOR_APPROVE_HOMEPAGE_WRITE" "wordpress\hcdecor-core\homepage-builder.php" >nul || exit /b 47
findstr /c:"HCDECOR_APPROVE_HOMEPAGE_WRITE" "wordpress\hcdecor-core\hcdecor-core.php" >nul || exit /b 48
findstr /c:"'post_status'=>'draft'" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 49
findstr /c:"approve_public_projects" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 50
findstr /c:"HCDECOR_APPROVE_CODE_SYNC" "wordpress\hcdecor-core\modules\background-sync.php" >nul || exit /b 56
findstr /c:"live_health" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 51
findstr /c:"hcdecor_conn_public_https" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 57
findstr /c:"wp_safe_remote_post" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 58
findstr /c:"Webhook URL must be public HTTPS." "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 59
findstr /c:"Unknown managed Drive folder" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 60
findstr /c:"hcdecor_drive_file_in_managed_folders" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 63
findstr /c:"Drive request blocked: untrusted API host." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 66
findstr /c:"Drive media file must be between 1 byte and 50 MB." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 64
findstr /c:"Approved or published jobs must be returned for changes before editing" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 65
findstr /c:"hcdecor_ai_worker_enabled() && function_exists('hcdecor_ai_available')" "wordpress\hcdecor-core\modules\agent-intake.php" >nul || exit /b 67
findstr /c:"hcdecor_ai_worker_enabled()) return;" "wordpress\hcdecor-core\modules\agent-intake.php" >nul || exit /b 68
findstr /c:"Restore source is outside the managed BACKUPS folder." "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 69
findstr /c:"Backup integrity metadata is required." "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 70
findstr /c:"hcdecor_ai_worker_enabled() && function_exists('hcdecor_ai_available')" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 71
findstr /c:"hcdecor_ai_worker_enabled()) return;" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 72
findstr /c:"AI worker master switch is disabled." "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 73
findstr /c:"if(empty($settings['enabled'])) return [];" "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 74
findstr /c:"$current['webhook_enabled']=false;" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 75
findstr /c:"$current['evergreen_enabled']=false;" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 76
findstr /c:"defined('HCDECOR_OPS_API_TOKEN')" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 77
findstr /c:"current_user_can('manage_options')" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 78
findstr /c:"HTTP_AUTHORIZATION" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 79
findstr /c:"hcdecor_ops_bridge_configured" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 80
findstr /c:"hc_last_publish_approved_by" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 81
findstr /c:"delete_post_meta($job_id,'hc_publish_approved_by')" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 82
findstr /c:"'approved_by'=>$approval_by" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 83
findstr /c:"delete_post_meta($id,'hc_publish_approved_by')" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 84
findstr /c:"hcdecor_project_vault_auto_sync_enabled" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 85
findstr /c:"hcdecor_project_vault_auto_sync_enabled()) return;" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 86
findstr /c:"wp_clear_scheduled_hook('hcdecor_project_vault_async_save')" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 87
findstr /c:"requires_credentials" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 88
findstr /c:"'live_health'=>" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 89
findstr /c:"'id'=>'project_to_ai'" "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 90
findstr /c:"'id'=>'review_to_drive'" "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 91
findstr /c:"hc_workspace_drive_save_requested_at" "wordpress\hcdecor-core\modules\ai-workspace.php" >nul || exit /b 92
findstr /c:"requires_credentials" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 93
findstr /c:"hcdecor_health_required_schedules" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 94
findstr /c:"required=hcdecor_health_required_schedules" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 95
findstr /c:"requireSameOriginMutation" "app\api\projects\route.js" >nul || exit /b 96
findstr /c:"requireSameOriginMutation" "app\api\projects\normalize\route.js" >nul || exit /b 97
findstr /c:"cross_origin_mutation_blocked" "lib\request-guard.js" >nul || exit /b 98
findstr /c:"cross_site_mutation_blocked" "lib\request-guard.js" >nul || exit /b 99
findstr /c:"'permission_callback'=>'hcdecor_ops_bridge_auth'" "wordpress\hcdecor-core\modules\media-manager.php" >nul || exit /b 100
findstr /c:"hcdecor_bridge_auth" "wordpress\hcdecor-core\modules\media-manager.php" >nul && exit /b 101
findstr /c:"HCDECOR_APPROVE_CODE_SYNC" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 102
findstr /c:"hcdecor_recovery_code_sync_approved" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 103
findstr /c:"hcdecor_recovery_trusted_raw_url" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 122
findstr /c:"wp_safe_remote_get" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 123
findstr /c:"wp_safe_remote_post('https://api.openai.com" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 126
findstr /c:"wp_safe_remote_post('https://generativelanguage.googleapis.com" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 127
findstr /c:"wp_safe_remote_request" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 128
findstr /c:"managed_folder_count" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 138
findstr /c:"hcdecor_run_background_sync" "wordpress\hcdecor-core\modules\hub-dashboard.php" >nul && exit /b 139
findstr /c:"!=='hc_project'" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 124
findstr /c:"evergreen_enabled" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 125
findstr /c:"cron_required['evergreen']" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 137
findstr /c:"hcdecor_sync_trusted_raw_url" "wordpress\hcdecor-core\modules\background-sync.php" >nul || exit /b 112
findstr /c:".bak-" "wordpress\hcdecor-core\modules\background-sync.php" >nul || exit /b 140
findstr /c:".hcbak" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 141
findstr /c:"raw.githubusercontent.com" "wordpress\hcdecor-core\modules\background-sync.php" >nul || exit /b 113
findstr /c:"wp_safe_remote_get(HCDECOR_SYNC_MANIFEST" "wordpress\hcdecor-core\modules\background-sync.php" >nul || exit /b 114
findstr /c:"hcdecor_drive_workflow_auto_sync_enabled" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 104
findstr /c:"hcdecor_drive_file_in_managed_folders($meta,['projects'])" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 145
findstr /c:"hc_drive_project_file_id',true),$file_id" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 146
findstr /c:"hc_drive_job_file_id',true),$file_id" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 147
findstr /c:"array_intersect(['web','facebook','tiktok','youtube']" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 148
findstr /c:"hcdecor_drive_workflow_auto_sync_enabled',false" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 105
findstr /c:"Automatic Drive workflow sync is disabled." "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 107
findstr /c:"hcdecor_conn_public_https($new['webhook_url'])" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 108
findstr /c:"Explicit production approval is required to retry social publishing." "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 161
findstr /c:"production_approval_consumed_at" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 165
findstr /c:"Social outbound attempted; fresh production approval is required before retry." "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 166
findstr /c:"Invalid workflow trigger" "wordpress\hcdecor-core\modules\workflow-crm.php" >nul || exit /b 167
findstr /c:"Invalid workflow action" "wordpress\hcdecor-core\modules\workflow-crm.php" >nul || exit /b 168
findstr /c:"Invalid inbox source" "wordpress\hcdecor-core\modules\workflow-crm.php" >nul || exit /b 169
findstr /c:"Invalid social publish schedule." "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 162
findstr /c:"get_post_type($id)==='attachment'" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 163
findstr /c:"['facebook','tiktok','youtube']" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 164
findstr /c:"webhook_configured" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 129
findstr /c:"current_user_can('manage_options')" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 109
findstr /c:"hcdecor_ops_valid_media_ids" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 149
findstr /c:"worker_disabled','AI Worker is disabled" "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 160
findstr /c:"in_array($cover,$media,true)" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 150
findstr /c:"HTTP_X_HCDECOR_BRIDGE" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 110
findstr /c:"HTTP_AUTHORIZATION" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 111
findstr /c:"hc_content_job') return;" "wordpress\hcdecor-core\modules\ai-workspace.php" >nul || exit /b 115
findstr /c:"get_post_type($id)==='attachment'" "wordpress\hcdecor-core\modules\agent-intake.php" >nul || exit /b 151
findstr /c:"current_user_can('edit_post',$project)" "wordpress\hcdecor-core\modules\media-manager.php" >nul || exit /b 170
findstr /c:"current_user_can('edit_post',$id)" "wordpress\hcdecor-core\modules\media-manager.php" >nul || exit /b 171
findstr /c:"current_user_can('edit_post',$project)" "wordpress\hcdecor-core\modules\ai-workspace.php" >nul || exit /b 172
findstr /c:"current_user_can('edit_post',$id)" "wordpress\hcdecor-core\modules\ai-workspace.php" >nul || exit /b 173
findstr /c:"current_user_can('edit_post',$id)" "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 174
findstr /c:"current_user_can('edit_post',$id)" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 175
findstr /c:"current_user_can('edit_post',$id)" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 176
findstr /c:"current_user_can('edit_post',$id)" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 177
findstr /c:"current_user_can('edit_post',$project)" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 178
findstr /c:"current_user_can('edit_post',$id)" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 179
findstr /c:"admin_post_hcdecor_project_vault_sync_all" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 180
findstr /c:"current_user_can('manage_options')" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 181
findstr /c:"admin_post_hcdecor_hub_sync_projects" "wordpress\hcdecor-core\modules\hub-dashboard.php" >nul || exit /b 182
findstr /c:"!=='attachment'" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 116
findstr /c:"wp_attachment_is_image" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 117
findstr /c:"TEST OK" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 118
findstr /c:"'ok'=>'TEST OK'" "wordpress\hcdecor-core\modules\ai-workspace.php" >nul || exit /b 120
findstr /c:"em.configured" "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 121
findstr /c:"delete_option('hcdecor_ai_'.$p.'_tested_at')" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 119
findstr /c:"has_backup_file" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 130
findstr /c:"hcdecor_backup_object_identity" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 156
findstr /c:"hcdecor_restore_existing_by_identity" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 157
findstr /c:"get_post_type($old)==='attachment'" "wordpress\hcdecor-core\modules\data-restore.php" >nul && exit /b 158
findstr /c:"if(!$project && get_post_type($old_project)==='hc_project')" "wordpress\hcdecor-core\modules\data-restore.php" >nul && exit /b 159
findstr /c:"drive_project_file_id" "wordpress\hcdecor-core\modules\data-backup.php" >nul && exit /b 134
findstr /c:"hc_drive_job_file_id" "wordpress\hcdecor-core\modules\data-restore.php" >nul && exit /b 135
findstr /c:"hc_drive_project_file_id" "wordpress\hcdecor-core\modules\data-restore.php" >nul && exit /b 152
findstr /c:"hc_drive_project_url" "wordpress\hcdecor-core\modules\data-restore.php" >nul && exit /b 153
findstr /c:"if(in_array($status,['approved','published_web'],true)) $status='review'" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 154
findstr /c:"delete_post_meta($id,'hc_publish_snapshot')" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 155
findstr /c:"'hc_drive_file_id'=>'drive_file_id'" "wordpress\hcdecor-core\modules\data-restore.php" >nul && exit /b 136
findstr /c:"hcdecor_runtime_safe_jobs" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 131
findstr /c:"hcdecor_runtime_prune" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 132
findstr /c:"$v[$id]['payload']=array()" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 142
findstr /c:"$v[$id]['production_approved']=false" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 143
findstr /c:"'body'=>substr" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul && exit /b 144
findstr /c:"crypto.randomBytes(3)" "lib\crm\google.js" >nul || exit /b 133
for %%F in (HCDecor-HUB-SERVICES.cmd HCDecor-HUB-START.cmd HCDecor-HUB-SYNC.cmd HCDecor-HUB-WATCH.cmd HCDecor-HUB-AUTO.cmd hcdecor-auto.cmd hcdecor-demo.cmd hcdecor-phase2.cmd hcdecor-phase2b.cmd) do findstr /c:"HCDECOR_APPROVE_CODE_SYNC" "wordpress\%%F" >nul || exit /b 106
findstr /c:"live_health'=>'not_checked'" "wordpress\hcdecor-core\modules\social-connectors.php" >nul || exit /b 52
findstr /c:"HCDECOR_APPROVE_SITE_CONFIG_WRITE" "wordpress\hcdecor-auto.cmd" >nul || exit /b 53
findstr /c:"HCDECOR_APPROVE_SITE_CONFIG_WRITE" "wordpress\setup-hcdecor.cmd" >nul || exit /b 54
findstr /i /r /c:"eval-file.*homepage-builder.php" wordpress\*.cmd wordpress\*.bat wordpress\*.ps1 >nul 2>nul && exit /b 55
echo Guards OK.
echo [6/7] Next.js build
call npm run build || exit /b 45
echo [7/7] Optional Git update
if /I "%~1"=="--pull" (git status --porcelain | findstr . >nul && (echo SKIP pull: working tree has changes.) || git pull --ff-only)
echo READY: production source checks passed.
exit /b 0
