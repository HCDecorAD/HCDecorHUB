[Reading 99 lines from start (total: 99 lines, 0 remaining)]

<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor managed background sync.
 * Pulls only files declared in the public manifest, verifies Git blob SHA, writes atomically.
 */
if (!defined('HCDECOR_SYNC_MANIFEST')) define('HCDECOR_SYNC_MANIFEST','https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress/hcdecor-sync-manifest.json');

add_filter('cron_schedules',function($s){$s['hcdecor_5min']=['interval'=>300,'display'=>'HCDecor every 5 minutes'];return $s;});
function hcdecor_background_sync_enabled(){ return (bool)get_option('hcdecor_background_sync_enabled',false); }
add_action('init',function(){
    if(hcdecor_background_sync_enabled()){
        if(!wp_next_scheduled('hcdecor_background_sync')) wp_schedule_event(time()+60,'hcdecor_5min','hcdecor_background_sync');
    }else{
        wp_clear_scheduled_hook('hcdecor_background_sync');
    }
});
add_action('hcdecor_background_sync',function(){ if(hcdecor_background_sync_enabled()) hcdecor_run_background_sync(); });

function hcdecor_sync_atomic($target,$body){
    $dir=dirname($target); if(!is_dir($dir)) wp_mkdir_p($dir);
    $tmp=$target.'.tmp-'.wp_generate_password(6,false,false);
    if(file_put_contents($tmp,$body,LOCK_EX)===false) return false;
    @chmod($tmp,0644);
    $backup='';
    if(file_exists($target)){
        $backup=$target.'.bak-'.wp_generate_password(6,false,false);
        if(!@copy($target,$backup)){@unlink($tmp);return false;}
        if(!@unlink($target)){@unlink($backup);@unlink($tmp);return false;}
    }
    if(!@rename($tmp,$target)){
        @unlink($tmp);
        if($backup!=='' && file_exists($backup)){ if(!@rename($backup,$target)) @copy($backup,$target); }
        return false;
    }
    if($backup!=='' && file_exists($backup)) @unlink($backup);
    return true;
}
function hcdecor_sync_trusted_raw_url($url,$rel=''){
    $url=is_scalar($url)?(string)$url:'';
    $parts=wp_parse_url($url);
    if(!is_array($parts)) return false;
    $scheme=is_scalar($parts['scheme']??'')?strtolower((string)$parts['scheme']):'';$host=is_scalar($parts['host']??'')?strtolower((string)$parts['host']):'';
    if($scheme!=='https' || $host!=='raw.githubusercontent.com') return false;
    $prefix='/HCDecorAD/HCDecorHUB/main/wordpress/hcdecor-core/';
    $path=is_scalar($parts['path']??'')?(string)$parts['path']:'';
    if($rel==='') return $path==='/HCDecorAD/HCDecorHUB/main/wordpress/hcdecor-sync-manifest.json';
    return $path===$prefix.ltrim((string)$rel,'/');
}
function hcdecor_run_background_sync(){
    if(defined('HCDECOR_APPROVE_CODE_SYNC') && HCDECOR_APPROVE_CODE_SYNC !== true){
        update_option('hcdecor_sync_last_error','approval-required');
        return false;
    }
    if(!hcdecor_sync_trusted_raw_url(HCDECOR_SYNC_MANIFEST)){ update_option('hcdecor_sync_last_error','manifest-url'); return false; }
    $r=wp_safe_remote_get(HCDECOR_SYNC_MANIFEST,['timeout'=>15,'limit_response_size'=>512*1024+1,'headers'=>['Cache-Control'=>'no-cache']]);
    if(is_wp_error($r)||wp_remote_retrieve_response_code($r)!==200){update_option('hcdecor_sync_last_error','manifest');return false;}
    $manifest_body=(string)wp_remote_retrieve_body($r);
    if($manifest_body==='' || strlen($manifest_body)>512*1024){update_option('hcdecor_sync_last_error','manifest-size');return false;}
    $m=json_decode($manifest_body,true);
    if(!$m||empty($m['files'])||!is_array($m['files'])||count($m['files'])>100){update_option('hcdecor_sync_last_error','manifest-json');return false;}
    $base=plugin_dir_path(__FILE__).'../';
    $changed=0;
    foreach($m['files'] as $f){
        $rel=ltrim((string)($f['path']??''),'/');
        if(!$rel||strpos($rel,'..')!==false||empty($f['url'])||empty($f['git_sha1'])) continue;
        if(!hcdecor_sync_trusted_raw_url($f['url'],$rel)) continue;
        $target=$base.$rel;
        $rr=wp_safe_remote_get($f['url'].(strpos($f['url'],'?')===false?'?':'&').'v='.rawurlencode((string)($m['version']??time())),['timeout'=>20,'limit_response_size'=>4*1024*1024+1,'headers'=>['Cache-Control'=>'no-cache']]);
        if(is_wp_error($rr)||wp_remote_retrieve_response_code($rr)!==200) continue;
        $body=(string)wp_remote_retrieve_body($rr);
        if($body==='' || strlen($body)>4*1024*1024) continue;
        $git_sha=sha1('blob '.strlen($body)."\0".$body); if(!hash_equals(strtolower($f['git_sha1']),$git_sha)) continue;
        if(file_exists($target)){ $local_size=@filesize($target); if($local_size!==false && $local_size>=0 && $local_size<=4*1024*1024){ $local=file_get_contents($target); if($local!==false && strlen($local)===(int)$local_size && sha1('blob '.strlen($local)."\0".$local)===strtolower($f['git_sha1'])) continue; } }
        if(hcdecor_sync_atomic($target,$body)) $changed++;
    }
    update_option('hcdecor_sync_version',sanitize_text_field($m['version']??''));
    update_option('hcdecor_sync_last',current_time('mysql'));
    update_option('hcdecor_sync_last_changed',$changed);
    delete_option('hcdecor_sync_last_error');
    return true;
}
add_action('admin_post_hcdecor_sync_now',function(){
    if(!current_user_can('manage_options'))wp_die('Forbidden');
    check_admin_referer('hcdecor_sync_now');
    hcdecor_run_background_sync();
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&synced=1'));exit;
});


/* HCDECOR_ADMIN_SELF_HEAL */
add_action('admin_init', function(){
    if(!current_user_can('manage_options') || !hcdecor_background_sync_enabled()) return;
    $last=(int)get_option('hcdecor_sync_last_epoch',0);
    if((time()-$last)<60) return;
    update_option('hcdecor_sync_last_epoch',time(),false);
    hcdecor_run_background_sync();
}, 1);

[executed on device: HOCUONG (a318a9bd-cfd6-4540-bf01-3ab9fb7f587a)]