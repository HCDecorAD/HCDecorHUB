<?php
/**
 * Plugin Name: HCDecor HUB Runtime
 * Description: HCDecor operational runtime. Safe-by-default; production writes remain explicitly gated.
 * Version: 2026.09.30.301
 * Author: HCDecor
 */
if (!defined('ABSPATH')) exit;

define('HCDECOR_RUNTIME_VERSION','2026.09.30.301');

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/runtime/status',[
        'methods'=>'GET','permission_callback'=>'__return_true',
        'callback'=>function(){
            $expected=['workflow-engine.php','content-operations.php','media-manager.php','media-intelligence.php','ai-providers.php','agent-intake.php','ai-workspace.php','web-publisher.php','project-publishing.php','connection-center.php','publish-runtime.php','social-connectors.php','social-manager.php','automation-hub.php','automation-recipes.php','drive-vault.php','drive-inbox.php','project-vault.php','data-backup.php','data-restore.php','workflow-crm.php','hub-suite.php','full-operations.php','background-sync.php','system-health.php','hub-dashboard.php','admin-cleanup.php','iam-runtime.php'];
            $loaded=0; foreach($expected as $m){if(is_readable(__DIR__.'/modules/'.$m)||is_readable(__DIR__.'/module-'.$m))$loaded++;}
            return rest_ensure_response(['ok'=>$loaded===count($expected),'version'=>HCDECOR_RUNTIME_VERSION,'module_files'=>$loaded,'module_expected'=>count($expected),'production_write'=>false]);
        }
    ]);
});

function hcdecor_runtime_register_content_types(){
    register_post_type('hc_project',[
        'labels'=>['name'=>'HCDecor Projects','singular_name'=>'HCDecor Project'],
        'public'=>true,'show_in_rest'=>true,'supports'=>['title','editor','thumbnail','excerpt'],
        'has_archive'=>true,'rewrite'=>['slug'=>'du-an-hcdecor']
    ]);
    register_taxonomy('hc_project_type',['hc_project'],[
        'labels'=>['name'=>'Project Types','singular_name'=>'Project Type'],
        'public'=>true,'show_in_rest'=>true,'hierarchical'=>true
    ]);
    register_post_type('hc_content_job',[
        'labels'=>['name'=>'HCDecor Content Jobs','singular_name'=>'HCDecor Content Job'],
        'public'=>false,'show_ui'=>true,'show_in_rest'=>false,'supports'=>['title','editor']
    ]);
}
add_action('init','hcdecor_runtime_register_content_types',5);
register_activation_hook(__FILE__,function(){hcdecor_runtime_register_content_types();flush_rewrite_rules();});
register_deactivation_hook(__FILE__,function(){flush_rewrite_rules();});

$hcdecor_modules=[
    'workflow-engine.php','content-operations.php','media-manager.php','media-intelligence.php',
    'ai-providers.php','agent-intake.php','ai-workspace.php','web-publisher.php','project-publishing.php',
    'connection-center.php','publish-runtime.php','social-connectors.php','social-manager.php',
    'automation-hub.php','automation-recipes.php','drive-vault.php','drive-inbox.php','project-vault.php',
    'data-backup.php','data-restore.php','workflow-crm.php','hub-suite.php','full-operations.php',
    'background-sync.php','system-health.php','hub-dashboard.php','admin-cleanup.php','iam-runtime.php'
];
foreach($hcdecor_modules as $hcdecor_module){
    $hcdecor_path=__DIR__.'/modules/'.$hcdecor_module;
    if(!is_readable($hcdecor_path)) $hcdecor_path=__DIR__.'/module-'.$hcdecor_module;
    if(is_readable($hcdecor_path)) require_once $hcdecor_path;
}
unset($hcdecor_module,$hcdecor_path,$hcdecor_modules);
