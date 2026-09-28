<?php
if (!defined('ABSPATH')) exit;

function hcdecor_social_settings(){
    $v=(array)get_option('hcdecor_social_connectors',[]);
    return wp_parse_args($v,[
        'facebook_page_id'=>'','facebook_token'=>'',
        'tiktok_client_key'=>'','tiktok_client_secret'=>'','tiktok_access_token'=>'',
        'youtube_channel_id'=>'','youtube_access_token'=>''
    ]);
}
function hcdecor_social_mask($v){$v=(string)$v;if($v==='')return 'Chưa cấu hình';$n=strlen($v);return $n<9?'••••••••':substr($v,0,4).'••••'.substr($v,-4);}
function hcdecor_social_channels(){ return ['facebook'=>'Facebook / Meta','tiktok'=>'TikTok','youtube'=>'YouTube']; }
function hcdecor_social_ready($channel,$s){
    if($channel==='facebook') return $s['facebook_page_id']!=='' && $s['facebook_token']!=='';
    if($channel==='tiktok') return $s['tiktok_client_key']!=='' && $s['tiktok_client_secret']!=='' && $s['tiktok_access_token']!=='';
    if($channel==='youtube') return $s['youtube_channel_id']!=='' && $s['youtube_access_token']!=='';
    return false;
}
add_action('admin_post_hcdecor_social_test',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    $channel=sanitize_key($_POST['channel']??'');check_admin_referer('hcdecor_social_test_'.$channel);
    $s=hcdecor_social_settings();$ok=hcdecor_social_ready($channel,$s);
    update_option('hcdecor_social_test_'.$channel,['configured'=>$ok,'at'=>current_time('mysql'),'message'=>$ok?'Credentials are configured; live API health was not checked.':'Connector credentials are incomplete.'],false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-social-connectors&tested='.$channel));exit;
});
function hcdecor_social_connection_state($channel){
    $s=hcdecor_social_settings();$ready=hcdecor_social_ready($channel,$s);$test=(array)get_option('hcdecor_social_test_'.$channel,[]);
    return ['configured'=>$ready,'tested'=>false,'tested_at'=>(string)($test['at']??''),'state'=>$ready?'configured':'requires-credentials','live_health'=>'not_checked'];
}
add_action('admin_post_hcdecor_social_save',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_social_save');
    $old=hcdecor_social_settings();
    $field=function($key,$secret=false)use($old){
        if(!isset($_POST[$key])) return (string)($old[$key]??'');
        $v=trim((string)wp_unslash($_POST[$key]));
        if($secret && $v==='') return (string)($old[$key]??'');
        return sanitize_text_field($v);
    };
    $new=[
        'facebook_page_id'=>$field('facebook_page_id'),
        'facebook_token'=>$field('facebook_token',true),
        'tiktok_client_key'=>$field('tiktok_client_key'),
        'tiktok_client_secret'=>$field('tiktok_client_secret',true),
        'tiktok_access_token'=>$field('tiktok_access_token',true),
        'youtube_channel_id'=>$field('youtube_channel_id'),
        'youtube_access_token'=>$field('youtube_access_token',true)
    ];
    $groups=[
        'facebook'=>['facebook_page_id','facebook_token'],
        'tiktok'=>['tiktok_client_key','tiktok_client_secret','tiktok_access_token'],
        'youtube'=>['youtube_channel_id','youtube_access_token']
    ];
    foreach($groups as $channel=>$keys){
        foreach($keys as $key){ if((string)($old[$key]??'')!==(string)($new[$key]??'')){ delete_option('hcdecor_social_test_'.$channel); break; } }
    }
    update_option('hcdecor_social_connectors',$new,false);
    update_option('hcdecor_social_connectors_updated_at',current_time('mysql'),false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-social-connectors&saved=1'));exit;
});
add_action('admin_post_hcdecor_social_disconnect',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    $channel=sanitize_key($_POST['channel']??'');check_admin_referer('hcdecor_social_disconnect_'.$channel);
    $s=hcdecor_social_settings();
    if($channel==='facebook'){$s['facebook_page_id']='';$s['facebook_token']='';}
    if($channel==='tiktok'){$s['tiktok_client_key']='';$s['tiktok_client_secret']='';$s['tiktok_access_token']='';}
    if($channel==='youtube'){$s['youtube_channel_id']='';$s['youtube_access_token']='';}
    update_option('hcdecor_social_connectors',$s,false);
    delete_option('hcdecor_social_test_'.$channel);
    update_option('hcdecor_social_connectors_updated_at',current_time('mysql'),false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-social-connectors'));exit;
});
add_action('admin_menu',function(){add_submenu_page('hcdecor-hub','Social Connectors','Social Connectors','manage_options','hcdecor-social-connectors','hcdecor_social_connectors_page',4);},27);
function hcdecor_social_connectors_page(){
    if(!current_user_can('manage_options')) return;$s=hcdecor_social_settings();
    $channels=[
      'facebook'=>['Facebook / Meta','dashicons-facebook','Page ID','facebook_page_id','Access Token','facebook_token'],
      'tiktok'=>['TikTok','dashicons-video-alt3','Client Key','tiktok_client_key','Access Token','tiktok_access_token'],
      'youtube'=>['YouTube','dashicons-video-alt','Channel ID','youtube_channel_id','Access Token','youtube_access_token']
    ];?>
    <div class="wrap hcsocial"><style>
    .hcsocial{max-width:1380px}.hcsocial-head{display:flex;justify-content:space-between;align-items:center;margin:18px 0}.hcsocial-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.hcs-card{background:#fff;border:1px solid #dcdcde;border-radius:16px;padding:18px}.hcs-top{display:flex;align-items:center;justify-content:space-between}.hcs-icon{width:44px;height:44px;border-radius:12px;background:#111827;color:#fff;display:grid;place-items:center}.hcs-icon .dashicons{font-size:24px;width:24px;height:24px}.hcs-state{font-size:11px;font-weight:800;padding:5px 9px;border-radius:99px;background:#fff1c7;color:#765800}.hcs-state.ok{background:#dcf5e5;color:#146c35}.hcs-card input{width:100%}.hcs-row{margin:14px 0}.hcs-row label{display:block;font-weight:700;margin-bottom:6px}.hcs-secret{font-family:monospace;color:#646970}.hcs-foot{display:flex;gap:8px;align-items:center;margin-top:16px}.hcs-flow{margin-top:16px;background:#0b1220;color:#dce6f5;border-radius:16px;padding:18px}.hcs-flow strong{color:#fff}.hcs-steps{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.hcs-step{border:1px solid #334155;border-radius:99px;padding:8px 12px}@media(max-width:900px){.hcsocial-grid{grid-template-columns:1fr}}
    </style><div class="hcsocial-head"><div><h1>Social Connectors</h1><p>Trung tâm kết nối và triển khai nội dung đa kênh.</p></div><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=hcdecor-automation'));?>">Automation HUB</a></div>
    <?php if(isset($_GET['saved'])):?><div class="notice notice-success inline"><p>Đã lưu cấu hình kết nối.</p></div><?php endif;?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="hcdecor_social_save"><?php wp_nonce_field('hcdecor_social_save');?>
    <div class="hcsocial-grid">
    <?php foreach($channels as $key=>$x):$ready=hcdecor_social_ready($key,$s);?>
      <section class="hcs-card"><div class="hcs-top"><div class="hcs-icon"><span class="dashicons <?php echo esc_attr($x[1]);?>"></span></div><span class="hcs-state <?php echo $ready?'ok':'';?>"><?php echo $ready?'CONFIGURED':'SETUP';?></span></div>
      <h2><?php echo esc_html($x[0]);?></h2>
      <div class="hcs-row"><label><?php echo esc_html($x[2]);?></label><input name="<?php echo esc_attr($x[3]);?>" value="<?php echo esc_attr($s[$x[3]]);?>" placeholder="<?php echo esc_attr($x[2]);?>"></div>
      <?php if($key==='tiktok'):?><div class="hcs-row"><label>Client Secret</label><input type="password" name="tiktok_client_secret" placeholder="<?php echo esc_attr(hcdecor_social_mask($s['tiktok_client_secret']));?>"></div><?php endif;?>
      <div class="hcs-row"><label><?php echo esc_html($x[4]);?></label><input type="password" name="<?php echo esc_attr($x[5]);?>" placeholder="<?php echo esc_attr(hcdecor_social_mask($s[$x[5]]));?>"></div>
      <div class="hcs-foot"><span class="hcs-secret"><?php echo esc_html($ready?'Credentials đã lưu':'Cần thông tin API'); ?></span><?php $test=(array)get_option('hcdecor_social_test_'.$key,[]);if(!empty($test['at'])):?><small><?php echo !empty($test['configured'])?' · CONFIGURED':' · CHECK';?></small><?php endif;?></div></section>
    <?php endforeach;?></div><p><button class="button button-primary button-hero">Lưu Social Connectors</button></p></form>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin:12px 0"><?php foreach(hcdecor_social_channels() as $key=>$label):?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="hcdecor_social_test"><input type="hidden" name="channel" value="<?php echo esc_attr($key);?>"><?php wp_nonce_field('hcdecor_social_test_'.$key);?><button class="button">Test <?php echo esc_html($label);?></button></form><?php endforeach;?></div><div class="hcs-flow"><strong>Publishing Flow</strong><div class="hcs-steps"><span class="hcs-step">Project</span><span class="hcs-step">AI Content</span><span class="hcs-step">Review</span><span class="hcs-step">Facebook</span><span class="hcs-step">TikTok</span><span class="hcs-step">YouTube</span><span class="hcs-step">Analytics</span></div></div>
    </div><?php
}
add_action('rest_api_init',function(){register_rest_route('hcdecor/v1','/social/status',['methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth','callback'=>function(){$s=hcdecor_social_settings();return rest_ensure_response(['facebook'=>hcdecor_social_connection_state('facebook'),'tiktok'=>hcdecor_social_connection_state('tiktok'),'youtube'=>hcdecor_social_connection_state('youtube'),'updated_at'=>(string)get_option('hcdecor_social_connectors_updated_at','')]);}]);});
