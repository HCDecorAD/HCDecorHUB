<?php
if (!defined('ABSPATH')) { exit; }

function hcdecor_social_accounts() {
    $value = get_option('hcdecor_social_accounts', []);
    return is_array($value) ? array_values($value) : [];
}
function hcdecor_social_groups() {
    $value = get_option('hcdecor_social_groups', []);
    return is_array($value) ? $value : [];
}
function hcdecor_social_account($id) {
    foreach (hcdecor_social_accounts() as $account) {
        if (($account['id'] ?? '') === $id) { return $account; }
    }
    return null;
}

add_action('admin_post_hcdecor_social_account_save', function () {
    if (!current_user_can('manage_options')) { wp_die('Forbidden'); }
    check_admin_referer('hcdecor_social_account_save');
    $all = hcdecor_social_accounts();
    $id = sanitize_key($_POST['account_id'] ?? '');
    if (!$id) { $id = 'acc_' . wp_generate_password(10, false, false); }
    $channel = sanitize_key($_POST['channel'] ?? '');
    if (!in_array($channel, ['facebook','tiktok','youtube'], true)) { wp_die('Invalid social channel'); }
    $row = [
        'id' => $id,
        'channel' => $channel,
        'name' => sanitize_text_field(wp_unslash($_POST['name'] ?? '')),
        'remote_id' => sanitize_text_field(wp_unslash($_POST['remote_id'] ?? '')),
        'token' => sanitize_text_field(wp_unslash($_POST['token'] ?? '')),
        'group' => sanitize_key($_POST['group'] ?? ''),
        'enabled' => !empty($_POST['enabled']),
    ];
    $found = false;
    foreach ($all as &$account) {
        if (($account['id'] ?? '') !== $id) { continue; }
        if ($row['token'] === '') { $row['token'] = $account['token'] ?? ''; }
        $account = $row;
        $found = true;
        break;
    }
    unset($account);
    if (!$found) { $all[] = $row; }
    update_option('hcdecor_social_accounts', $all, false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-social-manager&saved=1'));
    exit;
});

add_action('admin_post_hcdecor_social_group_save', function () {
    if (!current_user_can('manage_options')) { wp_die('Forbidden'); }
    check_admin_referer('hcdecor_social_group_save');
    $groups = hcdecor_social_groups();
    $key = sanitize_key($_POST['group_key'] ?? '');
    if ($key) { $groups[$key] = sanitize_text_field(wp_unslash($_POST['group_name'] ?? $key)); }
    update_option('hcdecor_social_groups', $groups, false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-social-manager'));
    exit;
});

add_action('admin_post_hcdecor_social_bulk_publish', function () {
    if (!current_user_can('manage_options')) { wp_die('Forbidden'); }
    check_admin_referer('hcdecor_social_bulk_publish');
    if (empty($_POST['production_approved']) || (string) $_POST['production_approved'] !== '1') {
        wp_die('Explicit production approval is required before social publishing can be queued.');
    }
    $ids = array_values(array_filter(array_map('sanitize_key', (array) ($_POST['accounts'] ?? []))));
    $group = sanitize_key($_POST['account_group'] ?? '');
    if ($group) {
        foreach (hcdecor_social_accounts() as $account) {
            if (($account['group'] ?? '') === $group) { $ids[] = $account['id']; }
        }
        $ids = array_values(array_unique($ids));
    }
    $media_raw = sanitize_text_field(wp_unslash($_POST['media_ids'] ?? ''));
    $media = array_slice(array_values(array_filter(array_unique(array_map('intval', preg_split('/[\s,]+/', $media_raw))), function($id){ return get_post_type($id)==='attachment'; })), 0, 60);
    $caption = sanitize_textarea_field(wp_unslash($_POST['caption'] ?? ''));
    $when = sanitize_text_field(wp_unslash($_POST['run_at'] ?? ''));
    $run = $when ? strtotime($when) : time();
    if ($when !== '' && $run === false) { wp_die('Invalid social publish schedule.'); }
    $run = max(time(), (int)$run);
    $batch = 'social_' . wp_generate_password(10, false, false);
    $created = 0;
    foreach ($ids as $id) {
        $account = hcdecor_social_account($id);
        if (!$account || empty($account['enabled']) || !in_array(($account['channel'] ?? ''), ['facebook','tiktok','youtube'], true)) { continue; }
        $payload = [
            'batch_id' => $batch,
            'account_id' => $id,
            'channel' => $account['channel'],
            'remote_id' => $account['remote_id'],
            'caption' => $caption,
            'media_ids' => $media,
            'status' => 'ready',
            'production_approved' => true,
            'production_approved_by' => get_current_user_id(),
            'production_approved_at' => current_time('mysql'),
        ];
        $result = function_exists('hcdecor_auto_enqueue')
            ? hcdecor_auto_enqueue('social_publish', $payload, $run, 'bulk:' . $batch . ':' . $id)
            : new WP_Error('automation', 'Automation unavailable');
        if (!is_wp_error($result)) {
            $created++;
            update_post_meta($result, 'hc_auto_account_id', $id);
            update_post_meta($result, 'hc_auto_batch_id', $batch);
        }
    }
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-social-manager&queued=' . $created));
    exit;
});

add_action('admin_menu', function () {
    add_submenu_page('hcdecor-hub', 'Social Manager', 'Social Manager', 'manage_options', 'hcdecor-social-manager', 'hcdecor_social_manager_page', 5);
}, 28);

function hcdecor_social_manager_page() {
    if (!current_user_can('manage_options')) { return; }
    $accounts = hcdecor_social_accounts();
    $groups = hcdecor_social_groups();
    ?>
    <div class="wrap hcsm">
      <style>.hcsm{max-width:1450px}.hcsm-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:14px}.hcsm-card{background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:16px}.hcsm-accounts{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.hcsm-account{border:1px solid #e4e4e7;border-radius:12px;padding:12px}.hcsm-account small{display:block;color:#646970}.hcsm-form input,.hcsm-form select,.hcsm-form textarea{width:100%;margin:5px 0 10px}.hcsm-check{display:flex;gap:7px;align-items:center;margin:7px 0}.hcsm-check input{width:auto;margin:0}@media(max-width:900px){.hcsm-grid,.hcsm-accounts{grid-template-columns:1fr}}</style>
      <h1>Social Manager · Multi Account</h1>
      <p>Chọn nhiều tài khoản, nhiều media, đăng ngay hoặc lên lịch theo batch.</p>
      <?php if (isset($_GET['queued'])) : ?><div class="notice notice-success inline"><p>Đã tạo <?php echo (int) $_GET['queued']; ?> job trong hàng đợi.</p></div><?php endif; ?>
      <div class="hcsm-grid">
        <section class="hcsm-card"><h2>Tài khoản</h2><div class="hcsm-accounts">
          <?php foreach ($accounts as $account) : ?><div class="hcsm-account"><strong><?php echo esc_html($account['name']); ?></strong><small><?php echo esc_html(strtoupper($account['channel']) . ' · ' . ($account['group'] ?? 'Không nhóm')); ?></small><small><?php echo !empty($account['enabled']) ? '● Active' : '○ Disabled'; ?></small></div><?php endforeach; ?>
          <?php if (!$accounts) : ?><p>Chưa có tài khoản.</p><?php endif; ?>
        </div>
        <h3>Thêm / cập nhật tài khoản</h3>
        <form class="hcsm-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="hcdecor_social_account_save"><?php wp_nonce_field('hcdecor_social_account_save'); ?><input name="name" placeholder="Tên tài khoản / Page"><select name="channel"><option value="facebook">Facebook</option><option value="tiktok">TikTok</option><option value="youtube">YouTube</option></select><input name="remote_id" placeholder="Page ID / Channel ID / Account ID"><input type="password" name="token" placeholder="Access token"><select name="group"><option value="">Không nhóm</option><?php foreach ($groups as $key => $name) : ?><option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($name); ?></option><?php endforeach; ?></select><label><input type="checkbox" name="enabled" value="1" checked> Active</label><p><button class="button button-primary">Lưu tài khoản</button></p></form>
        </section>
        <section class="hcsm-card"><h2>Nhóm tài khoản</h2>
          <form class="hcsm-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="hcdecor_social_group_save"><?php wp_nonce_field('hcdecor_social_group_save'); ?><input name="group_key" placeholder="vd: hcdecor"><input name="group_name" placeholder="HCDecor"><button class="button">Thêm nhóm</button></form>
          <hr><h2>Bulk Publisher</h2>
          <form class="hcsm-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="hcdecor_social_bulk_publish"><?php wp_nonce_field('hcdecor_social_bulk_publish'); ?><label>Chọn nhóm</label><select name="account_group"><option value="">—</option><?php foreach ($groups as $key => $name) : ?><option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($name); ?></option><?php endforeach; ?></select><label>Hoặc chọn nhiều tài khoản</label><?php foreach ($accounts as $account) : ?><label class="hcsm-check"><input type="checkbox" name="accounts[]" value="<?php echo esc_attr($account['id']); ?>"> <?php echo esc_html($account['name'] . ' · ' . strtoupper($account['channel'])); ?></label><?php endforeach; ?><label>Media IDs — chọn nhiều</label><input name="media_ids" placeholder="125, 126, 130"><label>Nội dung / Caption</label><textarea name="caption" rows="5"></textarea><label>Lịch đăng</label><input type="datetime-local" name="run_at"><label class="hcsm-check"><input type="checkbox" name="production_approved" value="1" required> Tôi xác nhận phê duyệt gửi nội dung này ra các kênh production đã chọn.</label><button class="button button-primary button-hero">Phê duyệt & đưa vào hàng đợi</button></form>
        </section>
      </div>
    </div>
    <?php
}

add_action('rest_api_init', function () {
    register_rest_route('hcdecor/v1', '/social/accounts', [
        'methods' => 'GET',
        'permission_callback' => 'hcdecor_ops_bridge_auth',
        'callback' => function () {
            $accounts = array_map(function ($account) {
                unset($account['token']);
                return $account;
            }, hcdecor_social_accounts());
            return rest_ensure_response(['accounts' => $accounts, 'groups' => hcdecor_social_groups()]);
        },
    ]);
});
