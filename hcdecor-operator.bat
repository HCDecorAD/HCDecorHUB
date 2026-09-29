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
findstr /c:"Fresh production approval required" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 46
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
findstr /c:"HCDECOR_APPROVE_CODE_SYNC" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 102
findstr /c:"hcdecor_recovery_code_sync_approved" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 103
findstr /c:"hcdecor_recovery_trusted_raw_url" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 122
findstr /c:"wp_safe_remote_get" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 123
findstr /c:"wp_safe_remote_post('https://api.openai.com" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 126
findstr /c:"wp_safe_remote_post('https://generativelanguage.googleapis.com" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 127
findstr /c:"wp_safe_remote_request" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 128
findstr /c:"managed_folder_count" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 138
findstr /c:"!=='hc_project'" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 124
findstr /c:"evergreen_enabled" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 125
findstr /c:"function hcdecor_health_required_schedules" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 137
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
findstr /c:"Explicit production approval is required to retry outbound delivery." "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 161
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
findstr /c:"approval_consumed_at" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 183
findstr /c:"Fresh production approval required." "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 184
findstr /c:"hash_equals((string)($old['secret']??''),(string)$secret)" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 185
findstr /c:"current_user_can('edit_post',$id)" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 186
findstr /c:"delete_option('hcdecor_social_test_'.$channel)" "wordpress\hcdecor-core\modules\social-connectors.php" >nul || exit /b 187
findstr /c:"delete_transient('hcdecor_drive_access_token')" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 188
findstr /c:"delete_option('hcdecor_drive_tested_at')" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 189
findstr /c:"Fresh production approval is required for webhook outbound" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 190
findstr /c:"Webhook outbound attempted; fresh production approval is required before retry." "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 191
findstr /c:"Fresh explicit production approval is required for recipe webhook delivery." "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 192
findstr /c:"$outbound_context=$context;" "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 193
findstr /c:"hcdecor_full_audit_sanitize" "wordpress\hcdecor-core\modules\full-operations.php" >nul || exit /b 194
findstr /c:"return '[REDACTED]'" "wordpress\hcdecor-core\modules\full-operations.php" >nul || exit /b 195
findstr /c:"$cron_required=hcdecor_health_required_schedules();" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 196
findstr /c:"$required=hcdecor_health_required_schedules();" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 197
findstr /c:"Retry</button>" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 198
findstr /c:"hcdecor_automation_retry" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 199
findstr /c:"Evergreen social publishing requires fresh explicit production approval." "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 200
findstr /c:"function hcdecor_runtime_approval_fresh" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 201
findstr /c:"15*MINUTE_IN_SECONDS" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 202
findstr /c:"'live_health'=>" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 203
findstr /c:"Production publish approval is not fresh; approve again." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 204
findstr /c:"array_slice($j['workflow_log'],-100)" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 205
findstr /c:"!wp_attachment_is_image($cover)" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 206
findstr /c:"Project Vault import requires a JSON file." "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 207
findstr /c:"array_unique(array_map('intval',(array)($snapshot['gallery']" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 208
findstr /c:"if($thumb && wp_attachment_is_image($thumb))" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 209
findstr /c:"never restore executable recipe structures raw" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 210
findstr /c:"$default['enabled']=false;" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 211
findstr /c:"array_slice((array)$v,-500,null,true)" "wordpress\hcdecor-core\modules\workflow-crm.php" >nul || exit /b 212
findstr /c:" Enabled</label>" "wordpress\hcdecor-core\modules\workflow-crm.php" >nul || exit /b 213
findstr /c:"array_slice(array_values(array_filter(array_unique(array_merge" "wordpress\hcdecor-core\modules\media-manager.php" >nul || exit /b 214
findstr /c:"array_slice(array_values(array_filter(array_unique(array_map('intval',array_merge" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 215
findstr /c:"else delete_post_thumbnail($id);" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 216
findstr /c:"Fresh explicit approval is required for Drive project writes." "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 218
findstr /c:"Fresh explicit approval is required for bulk Drive project writes." "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 219
findstr /c:"Fresh explicit approval is required for Drive job writes." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 220
findstr /c:"Backup exceeds the 25 MB restore limit." "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 221
findstr /c:"function hcdecor_social_accounts_safe" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 222
findstr /c:"function hcdecor_social_accounts_safe" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 223
findstr /c:"foreach(array_slice(array_values($value),-50)" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 224
findstr /c:"array_slice($groups, -50, null, true)" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 225
findstr /c:"function hcdecor_conn_save" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 226
findstr /c:"array_slice((array)$v,-100,null,true)" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 227
findstr /c:"dns_get_record" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 228
findstr /c:"function hcdecor_ai_safe_error" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 229
findstr /c:"$default['enabled']=false;" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 230
findstr /c:"update_option('hcdecor_automation_recipes',$recipes,false);" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 231
findstr /c:"current_user_can('manage_options')" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 232
findstr /c:"Drive Inbox is disabled." "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 233
findstr /c:"AI worker master switch is disabled." "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 234
findstr /c:"array_slice(array_values(array_filter(array_unique(array_map('intval'" "wordpress\hcdecor-core\modules\project-publishing.php" >nul || exit /b 235
findstr /c:"function hcdecor_ops_limit_text" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 236
findstr /c:"function hcdecor_drive_safe_error" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 237
findstr /c:"function hcdecor_auto_safe_message" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 238
findstr /c:"mb_substr($raw_msg,0,500)" "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 239
findstr /c:"function hcdecor_crm_textarea" "wordpress\hcdecor-core\modules\workflow-crm.php" >nul || exit /b 240
findstr /c:"hcdecor_crm_textarea(isset($_POST['message'])?$_POST['message']:'',5000)" "wordpress\hcdecor-core\modules\workflow-crm.php" >nul || exit /b 241
findstr /c:"function hcdecor_conn_safe_error" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 242
findstr /c:"hcdecor_drive_safe_error($r->get_error_message())" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 243
findstr /c:"hcdecor_drive_safe_error($r->get_error_message())" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 244
findstr /c:"Explicit production publish approval is required." "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 245
findstr /c:"name=\"production_approved\" value=\"1\" required" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 246
findstr /c:"Explicit production publish approval is required." "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 247
findstr /c:"name=\"production_approved\" value=\"1\"" "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 248
findstr /c:"function hcdecor_drive_inbox_safe_text" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 249
findstr /c:"add_submenu_page('hcdecor-hub','Drive Inbox','Drive Inbox','manage_options'" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 250
findstr /c:"Webhook payload exceeds 256 KB." "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 251
findstr /c:"Only failed or blocked automation tasks can be retried." "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 252
findstr /c:"'completed'=>true,'type'=>$type" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 253
findstr /c:"mb_substr($caption, 0, 10000)" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 254
findstr /c:"$limit=max(1,min(100,(int)($r->get_param('limit')?:100)))" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 255
findstr /c:"$limits=['projects'=>2000,'content_jobs'=>5000,'media_index'=>10000]" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 256
findstr /c:"'numberposts'=>2001" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 257
findstr /c:"'numberposts'=>5001" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 258
findstr /c:"'numberposts'=>10001" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 259
findstr /c:"if(is_wp_error($snap))" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 260
findstr /c:"array_slice((array)($d['tags']??[]),0,30)" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 261
findstr /c:"$clip($d['summary']??'',5000,true)" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 262
findstr /c:"hcdecor_ai_safe_error($msg)" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 263
findstr /c:"function_exists('hcdecor_drive_inbox_scan')?hcdecor_drive_inbox_scan()" "wordpress\hcdecor-core\modules\hub-dashboard.php" >nul || exit /b 264
findstr /c:"admin_post_hcdecor_hub_run_inbox" "wordpress\hcdecor-core\modules\hub-dashboard.php" >nul || exit /b 265
findstr /c:"Fresh explicit approval is required for Drive Inbox import or AI side effects." "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 266
findstr /c:"hcdecor_ops_limit_text(wp_unslash($_POST['brief']??''),20000)" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 267
findstr /c:"hcdecor_ops_limit_text(is_scalar($p['brief'])?(string)$p['brief']:'',20000)" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 268
findstr /c:"$clip($project['content']??'',100000,true)" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 269
findstr /c:"array_slice((array)$project['types'],0,30)" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 270
findstr /c:"hcdecor_ops_limit_text($brief,20000)" "wordpress\hcdecor-core\modules\agent-intake.php" >nul || exit /b 271
findstr /c:"hcdecor_ops_limit_text(wp_unslash($_POST['brief']??''),20000)" "wordpress\hcdecor-core\modules\ai-workspace.php" >nul || exit /b 272
findstr /c:"if($depth>=5) return '[DEPTH_LIMIT]';" "wordpress\hcdecor-core\modules\full-operations.php" >nul || exit /b 290
findstr /c:"array_slice($value,0,30,true)" "wordpress\hcdecor-core\modules\full-operations.php" >nul || exit /b 291
findstr /c:"getenv('HCDECOR_OPS_API_TOKEN')" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 293
findstr /c:"hcdecor_ops_bridge_configured():false" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 294
findstr /c:"new WP_Error('drive_token',hcdecor_drive_safe_error" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 296
findstr /c:"hcdecor_ops_limit_text(wp_unslash($_POST['agent_brief']??''),20000)" "wordpress\hcdecor-core\modules\media-manager.php" >nul || exit /b 273
findstr /c:"Runtime payload exceeds 256 KB." "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 274
findstr /c:"'Publish Runtime','Publish Runtime','manage_options'" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 275
findstr /c:"Automation payload exceeds 256 KB." "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 276
findstr /c:"'post_content'=>$encoded" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 277
findstr /c:"function hcdecor_restore_clip" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 278
findstr /c:"hcdecor_restore_clip($p['content']??'',100000,true)" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 279
findstr /c:"hcdecor_restore_clip($j['brief']??'',20000)" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 280
findstr /c:"array_slice((array)($m['ai_tags']??[]),0,30)" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 281
findstr /c:"$clip($d['job']['brief']??'',20000)" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 282
findstr /c:"mb_substr($prompt,0,20000)" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 283
findstr /c:"hcdecor_drive_active_prompt_title',$active_title" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 284
findstr /c:"hcdecor_restore_clip($s['active_prompt'],20000,true)" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 285
findstr /c:"function hcdecor_conn_safe_endpoint" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 286
findstr /c:"has_secret" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 287
findstr /c:"length>65536" "app\api\leads\route.js" >nul || exit /b 298
findstr /c:"'redirection'=>0" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 288
findstr /c:"'redirection'=>0" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 289
findstr /c:"'redirection'=>0" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 290
findstr /c:"'redirection'=>0" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 291
findstr /c:"'redirection'=>0" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 292
findstr /c:"identity_present" "wordpress\hcdecor-core\modules\system-health.php" >nul || exit /b 293
findstr /c:"function hcdecor_media_limit_text" "wordpress\hcdecor-core\modules\media-manager.php" >nul || exit /b 295
findstr /c:"hcdecor_media_limit_text(wp_unslash($_POST['description']??''),10000)" "wordpress\hcdecor-core\modules\media-manager.php" >nul || exit /b 296
findstr /c:"function hcdecor_social_limit_text" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 297
findstr /c:"8192" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 298
findstr /c:"8192:500" "wordpress\hcdecor-core\modules\social-connectors.php" >nul || exit /b 299
findstr /c:"array_key_exists" "wordpress\hcdecor-core\modules\social-connectors.php" >nul || exit /b 300
findstr /c:"mb_substr($note,0,5000)" "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 273
findstr /c:"$clip($project->post_content,30000)" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 274
findstr /c:"array_slice((array)get_post_meta($job_id,'hc_media_ids',true),0,12)" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 275
findstr /c:"$clip($drive_prompt,20000)" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 276
findstr /c:"!=='attachment'" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 116
findstr /c:"wp_attachment_is_image" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 117
findstr /c:"TEST OK" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 118
findstr /c:"'ok'=>'TEST OK'" "wordpress\hcdecor-core\modules\ai-workspace.php" >nul || exit /b 120
findstr /c:"em.configured" "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 121
findstr /c:"delete_option('hcdecor_ai_'.$p.'_tested_at')" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 119
findstr /c:"has_backup_file" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 130
findstr /c:"hcdecor_backup_object_identity" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 156
findstr /c:"hcdecor_restore_existing_by_identity" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 157
findstr /c:"if(in_array($status,['approved','published_web'],true)) $status='review'" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 154
findstr /c:"delete_post_meta($id,'hc_publish_snapshot')" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 155
findstr /c:"hcdecor_runtime_safe_jobs" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 131
findstr /c:"hcdecor_runtime_prune" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 132
findstr /c:"$v[$id]['payload']=array()" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 142
findstr /c:"$v[$id]['production_approved']=false" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 143
findstr /c:"crypto.randomBytes(3)" "lib\crm\google.js" >nul || exit /b 133
for %%F in (HCDecor-HUB-SERVICES.cmd HCDecor-HUB-START.cmd HCDecor-HUB-SYNC.cmd HCDecor-HUB-WATCH.cmd HCDecor-HUB-AUTO.cmd hcdecor-auto.cmd hcdecor-demo.cmd hcdecor-phase2.cmd hcdecor-phase2b.cmd) do findstr /c:"HCDECOR_APPROVE_CODE_SYNC" "wordpress\%%F" >nul || exit /b 106
findstr /c:"live_health'=>'not_checked'" "wordpress\hcdecor-core\modules\social-connectors.php" >nul || exit /b 52
findstr /c:"HCDECOR_APPROVE_SITE_CONFIG_WRITE" "wordpress\hcdecor-auto.cmd" >nul || exit /b 53
findstr /c:"HCDECOR_APPROVE_SITE_CONFIG_WRITE" "wordpress\setup-hcdecor.cmd" >nul || exit /b 54
findstr /c:"function hcdecor_restore_map_ids" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 301
findstr /c:"hcdecor_drive_safe_error" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 302
findstr /c:"'workflow_log'=>hcdecor_backup_workflow_log" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 303
findstr /c:"'ai_tags'=>hcdecor_backup_tags" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 304
findstr /c:"rest_sanitize_boolean" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 305
findstr /c:"current_user_can('edit_post'" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 306
findstr /c:"Fresh explicit production publish approval is required." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 307
findstr /c:"service_bridge" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 308
findstr /c:"hc_last_publish_approval_source" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 309
findstr /c:"hc_publish_approval_source" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 310
findstr /c:"hc_publish_approval_source" "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 311
findstr /c:"hc_publish_approval_source" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 312
findstr /c:"'gallery'=>array_slice" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 313
findstr /c:"Invalid content job." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 314
findstr /c:"'workflow_log'=>hcdecor_drive_job_log" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 315
findstr /c:"hcdecor_drive_inbox_safe_text($imported->get_error_message())" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 316
findstr /c:"function hcdecor_ai_usage_summary" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 317
findstr /c:"Unable to create content job." "wordpress\hcdecor-core\modules\agent-intake.php" >nul || exit /b 318
findstr /c:"Unable to create content job." "wordpress\hcdecor-core\modules\ai-workspace.php" >nul || exit /b 319
findstr /c:"Web publish failed." "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 320
findstr /c:"Web rollback failed." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 321
findstr /c:"Backup validation failed." "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 322
findstr /c:"Invalid project." "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 323
findstr /c:"Invalid media." "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 324
findstr /c:"Invalid Drive file ID." "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 325
findstr /c:"Invalid Drive file ID." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 326
findstr /c:"Invalid Drive file ID." "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 327
findstr /c:"Backup snapshot failed." "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 328
findstr /c:"Runtime delivery failed." "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 329
findstr /c:"Restore source must be a JSON backup file." "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 330
findstr /c:"Drive job import requires a JSON file." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 331
findstr /c:"Drive prompt import requires a JSON file." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 332
findstr /c:"function hcdecor_auto_approval_fresh" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 335
findstr /c:"Fresh production approval is required for social outbound" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 336
findstr /c:"function hcdecor_recipe_approval_fresh" "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 337
findstr /c:"Fresh explicit production approval is required for recipe webhook delivery." "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 338
findstr /c:"missing_origin_context" "lib\request-guard.js" >nul || exit /b 339
findstr /c:"request_too_large" "lib\request-guard.js" >nul || exit /b 340
findstr /c:"function hcdecor_backup_clip" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 341
findstr /c:"function hcdecor_backup_workflow_log" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 342
findstr /c:"function hcdecor_backup_tags" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 343
findstr /c:"0,60" "wordpress\hcdecor-core\modules\project-publishing.php" >nul || exit /b 344
findstr /c:"mb_substr((string)$p->post_excerpt,0,2000)" "wordpress\hcdecor-core\modules\project-publishing.php" >nul || exit /b 345
findstr /c:"Drive JSON payload exceeds 4 MB." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 346
findstr /c:"function hcdecor_project_vault_clip" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 347
findstr /c:"0,60" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 348
findstr /c:"0,60" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 349
findstr /c:"mb_substr($caption,0,5000)" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 350
findstr /c:"AbortSignal.timeout(10000)" "lib\crm\google.js" >nul || exit /b 351
findstr /c:"AbortSignal.timeout(5000)" "app\api\wp\status\route.js" >nul || exit /b 352
findstr /c:"AbortSignal.timeout(5000)" "lib\cms\wordpress.js" >nul || exit /b 353
findstr /c:".env.*" ".gitignore" >nul || exit /b 354
findstr /c:"!.env.example" ".gitignore" >nul || exit /b 355
findstr /c:"Project Vault operation failed." "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 356
findstr /c:"Project media sync failed." "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 357
findstr /c:"hcdecor_media_limit_text($m->post_content,10000)" "wordpress\hcdecor-core\modules\media-manager.php" >nul || exit /b 358
findstr /c:"hcdecor_media_limit_text(get_post_meta($m->ID,'_wp_attachment_image_alt',true),1000)" "wordpress\hcdecor-core\modules\media-manager.php" >nul || exit /b 359
findstr /c:"hcdecor_ops_valid_media_ids(hcdecor_ops_get" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 360
findstr /c:"hcdecor_ops_limit_text($post->post_content,20000)" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 361
findstr /c:"strlen($given)>512" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 362
findstr /c:"Invalid workflow status." "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 363
findstr /c:"function hcdecor_drive_clip" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 364
findstr /c:"function hcdecor_drive_job_log" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 365
findstr /c:"hcdecor_drive_job_log(get_post_meta" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 366
findstr /c:"Managed Drive folder is not configured." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 367
findstr /c:"strlen($manifest_body)>512*1024" "wordpress\hcdecor-core\modules\background-sync.php" >nul || exit /b 368
findstr /c:"count($m['files'])>100" "wordpress\hcdecor-core\modules\background-sync.php" >nul || exit /b 369
findstr /c:"strlen($body)>4*1024*1024" "wordpress\hcdecor-core\modules\background-sync.php" >nul || exit /b 370
findstr /c:"function hcdecor_conn_clip" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 371
findstr /c:"8192" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 372
findstr /c:"2048" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 373
findstr /c:"Drive media upload must be between 1 byte and 50 MB." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 374
findstr /c:"Cannot read attachment safely." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 375
findstr /c:"hcdecor_ops_valid_media_ids" "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 376
findstr /c:"strlen($token)>128" "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 377
findstr /c:"function hcdecor_runtime_clip" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 378
findstr /c:"hcdecor_runtime_prune(is_array($v)?$v:array())" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 379
findstr /c:"limit_response_size'=>2*1024*1024" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 380
findstr /c:"AI response exceeds 2 MB." "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 381
findstr /c:"hcdecor_ai_limit_text" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 382
findstr /c:"AI media response exceeds 2 MB." "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 383
findstr /c:"limit_response_size'=>2*1024*1024" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 384
findstr /c:"$title=$clip(get_the_title($attachment_id),500)" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 385
findstr /c:"$args['limit_response_size']=$args['limit_response_size']??4*1024*1024" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 386
findstr /c:"Drive download exceeds allowed size." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 387
findstr /c:"hcdecor_drive_download($file_id,25*1024*1024)" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 388
findstr /c:"strlen($raw)" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 389
findstr /c:"Cannot read image safely." "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 390
findstr /c:"$limit=$ch==='facebook'?10000:20000" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 391
findstr /c:"function hcdecor_publish_clip" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 392
findstr /c:"hcdecor_publish_clip($post->post_content,100000)" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 393
findstr /c:"hcdecor_publish_clip($snapshot['seo_meta']??'',2000)" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 394
findstr /c:"$approval_ts>time()+5*MINUTE_IN_SECONDS" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 395
findstr /c:"Production publish approval is not fresh; approve again." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 396
findstr /c:"limit_response_size'=>64*1024" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 397
findstr /c:"limit_response_size'=>64*1024" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 398
findstr /c:"limit_response_size'=>64*1024" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 399
findstr /c:"limit_response_size'=>512*1024+1" "wordpress\hcdecor-core\modules\background-sync.php" >nul || exit /b 400
findstr /c:"Manifest exceeds 512 KB" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 401
findstr /c:"count($m['files'])<=100" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 402
findstr /c:"limit_response_size'=>4*1024*1024+1" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 403
findstr /c:"$local_size=@filesize($target)" "wordpress\hcdecor-core\modules\background-sync.php" >nul || exit /b 404
findstr /c:"$local_size=@filesize($target)" "wordpress\hcdecor-core\recovery-bootstrap.php" >nul || exit /b 405
findstr /c:"production_approval_source']='wp_user'" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 406
findstr /c:"in_array($source,['wp_user','service_bridge'],true)" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 407
findstr /c:"$ids=array_slice(array_values(array_unique($ids)),0,50)" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 408
findstr /c:"fresh_external_write_approval_required" "app\api\projects\route.js" >nul || exit /b 409
findstr /c:"hcdecor_backup_clip(get_option('hcdecor_drive_active_prompt',''),20000)" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 410
findstr /c:"hcdecor_backup_clip(get_option('hcdecor_ai_openai_model',''),200)" "wordpress\hcdecor-core\modules\data-backup.php" >nul || exit /b 411
findstr /c:"function hcdecor_hub_dashboard_safe_error" "wordpress\hcdecor-core\modules\hub-dashboard.php" >nul || exit /b 412
findstr /c:"mb_substr($text,0,500)" "wordpress\hcdecor-core\modules\hub-dashboard.php" >nul || exit /b 413
findstr /c:"active_prompt_file'],300" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 414
findstr /c:"max(7,min(3650" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 415
findstr /c:"max(1,min(1440" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 416
findstr /c:"function hcdecor_social_groups_safe" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 417
findstr /c:"'remote_id'=>hcdecor_social_limit_text" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 418
findstr /c:"function hcdecor_full_audit_entries" "wordpress\hcdecor-core\modules\full-operations.php" >nul || exit /b 419
findstr /c:"array_slice($v,-100,null,true)" "wordpress\hcdecor-core\modules\hub-suite.php" >nul || exit /b 420
findstr /c:"mb_substr($prompt_title,0,300)" "wordpress\hcdecor-core\modules\ai-workspace.php" >nul || exit /b 421
findstr /c:"strlen($state)>256" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 422
findstr /c:"strlen($code)>8192" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 423
findstr /c:"[\"same-origin\",\"none\"]" "lib\request-guard.js" >nul || exit /b 424
findstr /c:"function hcdecor_drive_inbox_approval_fresh" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 425
findstr /c:"Fresh explicit approval is required for Drive Inbox import" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 426
findstr /c:"function hcdecor_drive_approval_fresh" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 427
findstr /c:"Fresh explicit approval is required for Drive imports." "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 428
findstr /c:"Fresh explicit approval is required for Drive project imports." "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 429
findstr /c:"$limit=max(1,min(100,(int)$limit));" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 430
findstr /c:"function hcdecor_media_rest_approval_fresh" "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 431
findstr /c:"Fresh explicit approval is required for AI media analysis." "wordpress\hcdecor-core\modules\media-intelligence.php" >nul || exit /b 432
findstr /c:"strlen($at_raw)>64" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 433
findstr /c:"production_approval_source'=>sanitize_key" "wordpress\hcdecor-core\modules\automation-recipes.php" >nul || exit /b 434
findstr /c:"production_approval_source'=>hcdecor_runtime_clip" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 435
findstr /c:"$at<=time()+5*MINUTE_IN_SECONDS" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 436
findstr /c:"function hcdecor_runtime_time" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 437
findstr /c:"Invalid runtime schedule." "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 438
findstr /c:"hcdecor-projects" "app\api\projects\route.js" >nul || exit /b 439
findstr /c:"limit=10" "app\api\projects\route.js" >nul || exit /b 440
findstr /c:"leadId.length>200" "app\api\projects\route.js" >nul || exit /b 441
findstr /c:"buckets.size>=5000" "app\api\leads\route.js" >nul || exit /b 442
findstr /c:"buckets.size>=5000" "app\api\projects\route.js" >nul || exit /b 443
findstr /c:"trim().slice(0,256)" "app\api\leads\route.js" >nul || exit /b 444
findstr /c:"foreach($defaults as $key=>$default)" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 445
findstr /c:"preg_replace('/[^A-Za-z0-9_-]/','',$value)" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 446
findstr /c:"strlen($client_id)>1000" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 447
findstr /c:"'refresh_token'=>8000" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 448
findstr /c:"strlen($access)>16000" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 449
findstr /c:"min(DAY_IN_SECONDS" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 450
findstr /c:"function hcdecor_ai_bounded_scalar" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 451
findstr /c:"HCDECOR_OPENAI_API_KEY,8000" "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 452
findstr /c:"AI model value too long." "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 453
findstr /c:"AI credential value too long." "wordpress\hcdecor-core\modules\ai-providers.php" >nul || exit /b 454
findstr /c:"function hcdecor_conn_normalize" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 455
findstr /c:"Connection secret is too long." "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 456
findstr /c:"hcdecor_conn_clip($x['secret']" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 457
findstr /c:"$limits=['client_id'=>1000,'client_secret'=>4000,'refresh_token'=>8000]" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 458
findstr /c:"strlen($cached)<=16000" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 459
findstr /c:"add_menu_page('HCDecor HUB','HCDecor HUB','edit_posts','hcdecor-hub','hcdecor_hub_dashboard_page'" "wordpress\hcdecor-core\modules\hub-dashboard.php" >nul || exit /b 460
findstr /c:"content_length_required" "lib\request-guard.js" >nul || exit /b 461
findstr /c:"fresh_external_write_approval_required" "app\api\projects\route.js" >nul || exit /b 462
findstr /c:"approvalSource" "app\api\projects\route.js" >nul || exit /b 463
findstr /c:"hc_publish_approval_source','wp_user'" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 464
findstr /c:"hc_publish_approval_source','wp_user'" "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 465
findstr /c:"in_array($approval_source,['wp_user','service_bridge'],true)" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 466
findstr /c:"Web publish content exceeds safe limits." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 467
findstr /c:"$published_snapshot=hcdecor_publish_snapshot($project)" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 468
findstr /c:"published state was restored." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 469
findstr /c:"function hcdecor_publish_rest_approval_fresh" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 470
findstr /c:"Fresh explicit production publish approval is required." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 471
findstr /c:"ctype_digit($by)" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 472
findstr /c:"HCDECOR_PROJECT_API_TOKEN=" ".env.example" >nul || exit /b 473
findstr /c:"projectApiAuthConfigured" "lib\crm\config.js" >nul || exit /b 474
findstr /c:"crypto.timingSafeEqual" "app\api\projects\route.js" >nul || exit /b 475
findstr /c:"error:\"unauthorized\"" "app\api\projects\route.js" >nul || exit /b 476
findstr /c:"$len>=32 && $len<=512" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 477
findstr /c:"function hcdecor_ops_bridge_auth($request=null)" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 478
findstr /c:"(int)$raw>65536" "wordpress\hcdecor-core\modules\content-operations.php" >nul || exit /b 479
findstr /c:"HCDECOR_OPS_API_TOKEN=" ".env.example" >nul || exit /b 480
findstr /c:"strlen($claimed_raw)<=64" "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 481
findstr /c:"$token_raw=$r->get_param('claim_token')" "wordpress\hcdecor-core\modules\workflow-engine.php" >nul || exit /b 482
findstr /c:"const projectApiAuth=Boolean" "lib\data\store.js" >nul || exit /b 483
findstr /c:"&&projectFolder&&projectApiAuth" "lib\data\store.js" >nul || exit /b 484
findstr /c:"array_key_exists('auto_link_project',$saved)" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 485
findstr /c:"'limit'=>max(1,min(50" "wordpress\hcdecor-core\modules\drive-inbox.php" >nul || exit /b 486
findstr /c:"Social publish schedule must run within 10 minutes" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 487
findstr /c:"strlen($caption_input)>20000" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 488
findstr /c:"strlen($media_input)>5000" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 489
findstr /c:"function hcdecor_social_account_normalize" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 490
findstr /c:"Social credential value too long." "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 491
findstr /c:"if(strlen($token)>8192)$token=''" "wordpress\hcdecor-core\modules\social-manager.php" >nul || exit /b 492
findstr /c:"$limits=['facebook_page_id'=>500" "wordpress\hcdecor-core\modules\social-connectors.php" >nul || exit /b 493
findstr /c:"Social connector credential is too long." "wordpress\hcdecor-core\modules\social-connectors.php" >nul || exit /b 494
findstr /c:"is_scalar($channel_raw)" "wordpress\hcdecor-core\modules\social-connectors.php" >nul || exit /b 495
echo Guards OK.
echo [6/7] Next.js build
call npm run build || exit /b 45
echo [7/7] Optional Git update
if /I "%~1"=="--pull" (git status --porcelain | findstr . >nul && (echo SKIP pull: working tree has changes.) || git pull --ff-only)
echo READY: production source checks passed.
exit /b 0
