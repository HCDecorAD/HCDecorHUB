<?php
if (!defined('ABSPATH')) exit;

/* Keep legacy business data available by direct URL, but remove obsolete demo/CRM menus from HUB. */
add_action('admin_menu',function(){
    remove_submenu_page('hcdecor-hub','hcdecor-agent');
    remove_submenu_page('hcdecor-hub','edit.php?post_type=hc_content_job');
    remove_submenu_page('hcdecor-hub','upload.php');
    remove_submenu_page('hcdecor-hub','hcdecor-pipeline');
    remove_submenu_page('hcdecor-hub','hcdecor-studio');
    remove_submenu_page('hcdecor-hub','hcdecor-studio-v2');
    remove_menu_page('edit.php?post_type=hc_quote');
    remove_menu_page('edit.php?post_type=hc_lead');
},999);

add_filter('parent_file',function($parent){
    if(isset($_GET['page']) && in_array($_GET['page'],['hcdecor-ai-workspace','hcdecor-content-operations','hcdecor-review','hcdecor-media','hcdecor-ai-providers','hcdecor-automation','hcdecor-drive-vault','hcdecor-drive-inbox','hcdecor-data-backups','hcdecor-restore-center','hcdecor-system-health','hcdecor-bridge'],true)) return 'hcdecor-hub';
    return $parent;
});
