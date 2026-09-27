<?php
if (!defined('ABSPATH')) { exit; }
/**
 * HCDecor Recovery Bootstrap
 * Runs before feature modules. It can refresh managed files from GitHub and
 * quarantine a module that still fails PHP lint, preventing one bad module
 * from taking the whole WordPress admin down on the next request.
 */
if (!defined('HCDECOR_SYNC_MANIFEST')) {
    define('HCDECOR_SYNC_MANIFEST', 'https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress/hcdecor-sync-manifest.json');
}
function hcdecor_recovery_git_sha($body) { return sha1('blob ' . strlen($body) . "\0" . $body); }
function hcdecor_recovery_fetch_manifest() {
    $url = HCDECOR_SYNC_MANIFEST . '?t=' . time();
    $r = wp_remote_get($url, ['timeout'=>15,'headers'=>['Cache-Control'=>'no-cache']]);
    if (is_wp_error($r) || wp_remote_retrieve_response_code($r) !== 200) { return new WP_Error('manifest','Manifest unavailable'); }
    $m = json_decode(wp_remote_retrieve_body($r), true);
    return (!empty($m['files']) && is_array($m['files'])) ? $m : new WP_Error('manifest_json','Invalid manifest');
}
function hcdecor_recovery_atomic($target, $body) {
    $dir = dirname($target); if (!is_dir($dir)) { wp_mkdir_p($dir); }
    $tmp = $target . '.hcnew';
    if (file_put_contents($tmp, $body, LOCK_EX) === false) { return false; }
    @chmod($tmp, 0644);
    if (!@rename($tmp, $target)) {
        @unlink($target);
        if (!@rename($tmp, $target)) { @unlink($tmp); return false; }
    }
    return true;
}
function hcdecor_recovery_sync($force=false) {
    $last = (int) get_option('hcdecor_recovery_epoch', 0);
    if (!$force && time() - $last < 300) { return true; }
    $m = hcdecor_recovery_fetch_manifest();
    if (is_wp_error($m)) { update_option('hcdecor_sync_last_error',$m->get_error_code(),false); return false; }
    $base = plugin_dir_path(__FILE__);
    $changed = 0;
    foreach ($m['files'] as $f) {
        $rel = ltrim((string)($f['path'] ?? ''), '/');
        if (!$rel || strpos($rel, '..') !== false || empty($f['url']) || empty($f['git_sha1'])) { continue; }
        $target = $base . $rel;
        if (!$force && is_file($target)) {
            $local = @file_get_contents($target);
            if ($local !== false && hash_equals(strtolower($f['git_sha1']), hcdecor_recovery_git_sha($local))) { continue; }
        }
        $url = $f['url'] . (strpos($f['url'],'?') === false ? '?' : '&') . 'v=' . rawurlencode((string)($m['version'] ?? time()));
        $r = wp_remote_get($url, ['timeout'=>20,'headers'=>['Cache-Control'=>'no-cache']]);
        if (is_wp_error($r) || wp_remote_retrieve_response_code($r) !== 200) { continue; }
        $body = wp_remote_retrieve_body($r);
        if (!hash_equals(strtolower($f['git_sha1']), hcdecor_recovery_git_sha($body))) { continue; }
        if (hcdecor_recovery_atomic($target, $body)) { $changed++; }
    }
    update_option('hcdecor_recovery_epoch', time(), false);
    update_option('hcdecor_sync_version', sanitize_text_field($m['version'] ?? ''), false);
    update_option('hcdecor_sync_last', current_time('mysql'), false);
    update_option('hcdecor_sync_last_changed', $changed, false);
    delete_option('hcdecor_sync_last_error');
    return true;
}
add_action('admin_init', function(){ if (current_user_can('manage_options')) { hcdecor_recovery_sync(false); } }, 0);
add_action('admin_post_hcdecor_recovery_sync', function(){
    if (!current_user_can('manage_options')) { wp_die('Forbidden'); }
    check_admin_referer('hcdecor_recovery_sync');
    hcdecor_recovery_sync(true);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-system-health&synced=1')); exit;
});
