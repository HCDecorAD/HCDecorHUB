<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor Drive Vault
 * Google Drive = long-term data vault.
 * WordPress = workflow/index/queue.
 */

function hcdecor_drive_default_folders(){
    return [
        'root'=>'1NqJuLhum63XVea8wPjZtTETl29bm3U7r',
        'command'=>'1Y9Wx8MoHnMxWSyO-mkOa5aidfnp7F8xg',
        'projects'=>'1bKNzYBoDQ-FKb0KegVk1NUinmvV6B5xy',
        'content'=>'1zZkNEh8pOFGaxQVx2QzX7ZO3BJa1OqwE',
        'media'=>'1OIz9cC8X2ztLQk7Qmr9lquQPEDj24T6G',
        'knowledge'=>'1yT-EP7cL1BmBYoojcy2SLU30Yl45Ih6p',
        'templates'=>'1s17AIpqVLv0kUJHhoby3GXRqwL_KzPHY',
        'reports'=>'19MzucieRW3LT9Dq9V_XVNA-VasxNR_iD',
        'automation'=>'1Z55POM2C69WHb8O1fb04YcQ2a7Q9cI1V',
        'archive'=>'1GFNq7Hi0-hRJc11QtFuX7_9LRGfhpq9Z',
        'prompts'=>'1yi00jU--BcjYR1h1cTzSR73F7Xk5yJa-',
        'ai_presets'=>'1eAKZ3Wuy6R2ppNvCT1GVI5f0v8iUuDHc',
        'brand'=>'1w1tygDovmgVN19qmJV51Lo1FokwAHLWE',
        'manifests'=>'1MOgplCZJ9t2BYvoMWqJNzQojLwzdzdv_',
        'content_draft'=>'1z5PXaXro3Q-A0auzPXPkfYMq3ynJ4qSX',
        'content_review'=>'1p-gOd6bOrIgBHMN66SNBMBSf7hVj_Efg',
        'content_approved'=>'14NNMdL8sm5AyGbqIS9Ws17zYcZwQhL6B',
        'content_published'=>'17e1beNodrLcOCfmSKR7TWWkIfoy29yNz',
        'media_input'=>'13xvHhVARk-VqwLwro62bQWTV1rBqc8e4',
        'media_ai'=>'1s1ND4y-ziO3FiQ5rUPbh0Apx0N70LGNw',
        'media_approved'=>'19PLqjuwPbbK_6xIhyunloISYzPLhwh6g',
        'jobs'=>'19GoViim4G1fOHvN2_DQ4uxdBfxT4ndRg',
        'logs'=>'1fuAtzdIH3hzUtArr3dl4r94WBeLRNNih',
        'exports'=>'1ZASnBdvo2snkREvSzMuCSgS8hZX5dnWj'
    ];
}

function hcdecor_drive_folders(){
    return wp_parse_args((array)get_option('hcdecor_drive_folders',[]),hcdecor_drive_default_folders());
}

function hcdecor_drive_secret($name){
    $const=[
        'client_id'=>'HCDECOR_GOOGLE_CLIENT_ID',
        'client_secret'=>'HCDECOR_GOOGLE_CLIENT_SECRET',
        'refresh_token'=>'HCDECOR_GOOGLE_REFRESH_TOKEN'
    ];
    if(isset($const[$name]) && defined($const[$name])) return trim((string)constant($const[$name]));
    return trim((string)get_option('hcdecor_drive_'.$name,''));
}

function hcdecor_drive_configured(){
    return hcdecor_drive_secret('client_id')!=='' && hcdecor_drive_secret('client_secret')!=='' && hcdecor_drive_secret('refresh_token')!=='';
}

function hcdecor_drive_oauth_redirect_uri(){
    return admin_url('admin-post.php?action=hcdecor_drive_oauth_callback');
}

function hcdecor_drive_oauth_connect_url(){
    $client=hcdecor_drive_secret('client_id');
    if($client==='') return '';
    $state=wp_create_nonce('hcdecor_drive_oauth_state');
    return add_query_arg([
        'client_id'=>$client,
        'redirect_uri'=>hcdecor_drive_oauth_redirect_uri(),
        'response_type'=>'code',
        'scope'=>'https://www.googleapis.com/auth/drive',
        'access_type'=>'offline',
        'prompt'=>'consent',
        'include_granted_scopes'=>'true',
        'state'=>$state
    ],'https://accounts.google.com/o/oauth2/v2/auth');
}

add_action('admin_post_hcdecor_drive_oauth_start',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_drive_oauth_start');
    if(hcdecor_drive_secret('client_id')==='' || hcdecor_drive_secret('client_secret')===''){
        wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&oauth_missing=1')); exit;
    }
    wp_redirect(hcdecor_drive_oauth_connect_url()); exit;
});

add_action('admin_post_hcdecor_drive_oauth_callback',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    $state=(string)($_GET['state']??'');
    if(!$state || !wp_verify_nonce($state,'hcdecor_drive_oauth_state')) wp_die('Invalid OAuth state.');
    if(!empty($_GET['error'])){
        wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&oauth_error='.rawurlencode(sanitize_text_field(wp_unslash($_GET['error']))))); exit;
    }
    $code=(string)wp_unslash($_GET['code']??'');
    if($code==='') wp_die('Missing OAuth code.');
    $r=wp_remote_post('https://oauth2.googleapis.com/token',[
        'timeout'=>30,
        'body'=>[
            'code'=>$code,
            'client_id'=>hcdecor_drive_secret('client_id'),
            'client_secret'=>hcdecor_drive_secret('client_secret'),
            'redirect_uri'=>hcdecor_drive_oauth_redirect_uri(),
            'grant_type'=>'authorization_code'
        ]
    ]);
    if(is_wp_error($r)){
        update_option('hcdecor_drive_test_status','error',false);
        update_option('hcdecor_drive_test_message',$r->get_error_message(),false);
        wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&oauth_failed=1')); exit;
    }
    $status=(int)wp_remote_retrieve_response_code($r);
    $data=json_decode(wp_remote_retrieve_body($r),true);
    if($status<200 || $status>=300 || empty($data['access_token'])){
        update_option('hcdecor_drive_test_status','error',false);
        update_option('hcdecor_drive_test_message',sanitize_text_field((string)($data['error_description']??$data['error']??('OAuth HTTP '.$status))),false);
        wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&oauth_failed=1')); exit;
    }
    if(!empty($data['refresh_token'])) update_option('hcdecor_drive_refresh_token',sanitize_text_field($data['refresh_token']),false);
    set_transient('hcdecor_drive_access_token',sanitize_text_field($data['access_token']),max(60,(int)($data['expires_in']??3600)-120));
    $test=hcdecor_drive_test();
    if(is_wp_error($test)){
        update_option('hcdecor_drive_test_status','error',false);
        update_option('hcdecor_drive_test_message',$test->get_error_message(),false);
    }else{
        update_option('hcdecor_drive_test_status','ok',false);
        update_option('hcdecor_drive_test_message',sanitize_text_field((string)($test['user']['emailAddress']??'Connected')),false);
        update_option('hcdecor_drive_connected_email',sanitize_email((string)($test['user']['emailAddress']??'')),false);
        update_option('hcdecor_drive_connected_at',current_time('mysql'),false);
    }
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&oauth_connected=1')); exit;
});

add_action('admin_post_hcdecor_drive_disconnect',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_drive_disconnect');
    delete_option('hcdecor_drive_refresh_token');
    delete_option('hcdecor_drive_connected_email');
    delete_option('hcdecor_drive_connected_at');
    delete_option('hcdecor_drive_test_status');
    delete_option('hcdecor_drive_test_message');
    delete_transient('hcdecor_drive_access_token');
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&disconnected=1')); exit;
});

function hcdecor_drive_access_token($force=false){
    if(!$force){
        $cached=get_transient('hcdecor_drive_access_token');
        if(is_string($cached)&&$cached!=='') return $cached;
    }
    if(!hcdecor_drive_configured()) return new WP_Error('drive_auth','Google Drive chưa kết nối.');
    $r=wp_remote_post('https://oauth2.googleapis.com/token',[
        'timeout'=>25,
        'body'=>[
            'client_id'=>hcdecor_drive_secret('client_id'),
            'client_secret'=>hcdecor_drive_secret('client_secret'),
            'refresh_token'=>hcdecor_drive_secret('refresh_token'),
            'grant_type'=>'refresh_token'
        ]
    ]);
    if(is_wp_error($r)) return $r;
    $code=(int)wp_remote_retrieve_response_code($r);
    $data=json_decode(wp_remote_retrieve_body($r),true);
    if($code<200||$code>=300||empty($data['access_token'])){
        return new WP_Error('drive_token',(string)($data['error_description']??$data['error']??('Token HTTP '.$code)));
    }
    $ttl=max(60,(int)($data['expires_in']??3600)-120);
    set_transient('hcdecor_drive_access_token',(string)$data['access_token'],$ttl);
    return (string)$data['access_token'];
}

function hcdecor_drive_request($method,$url,$args=[]){
    $token=hcdecor_drive_access_token();
    if(is_wp_error($token)) return $token;
    $headers=(array)($args['headers']??[]);
    $headers['Authorization']='Bearer '.$token;
    $args['headers']=$headers;
    $args['method']=$method;
    $args['timeout']=$args['timeout']??60;
    $r=wp_remote_request($url,$args);
    if(is_wp_error($r)) return $r;
    $code=(int)wp_remote_retrieve_response_code($r);
    if($code===401){
        delete_transient('hcdecor_drive_access_token');
        $token=hcdecor_drive_access_token(true);
        if(is_wp_error($token)) return $token;
        $args['headers']['Authorization']='Bearer '.$token;
        $r=wp_remote_request($url,$args);
        if(is_wp_error($r)) return $r;
        $code=(int)wp_remote_retrieve_response_code($r);
    }
    $body=wp_remote_retrieve_body($r);
    if($code<200||$code>=300){
        $j=json_decode($body,true);
        return new WP_Error('drive_http',(string)($j['error']['message']??('Drive HTTP '.$code)),['status'=>$code,'body'=>$body]);
    }
    return ['code'=>$code,'body'=>$body,'headers'=>wp_remote_retrieve_headers($r)];
}

function hcdecor_drive_test(){
    $r=hcdecor_drive_request('GET','https://www.googleapis.com/drive/v3/about?fields=user,storageQuota');
    if(is_wp_error($r)) return $r;
    $d=json_decode($r['body'],true);
    return is_array($d)?$d:new WP_Error('drive_json','Drive response invalid.');
}

function hcdecor_drive_list($folder_id,$limit=100){
    $folder_id=preg_replace('/[^A-Za-z0-9_-]/','',(string)$folder_id);
    if(!$folder_id) return new WP_Error('folder','Invalid folder.');
    $q=rawurlencode("'".$folder_id."' in parents and trashed=false");
    $fields=rawurlencode('files(id,name,mimeType,size,modifiedTime,webViewLink,thumbnailLink,description)');
    $url='https://www.googleapis.com/drive/v3/files?q='.$q.'&pageSize='.max(1,min(1000,(int)$limit)).'&orderBy=modifiedTime%20desc&fields='.$fields;
    $r=hcdecor_drive_request('GET',$url);
    if(is_wp_error($r)) return $r;
    $d=json_decode($r['body'],true);
    return (array)($d['files']??[]);
}

function hcdecor_drive_multipart($file_id,$name,$mime,$bytes,$parent_id=''){
    $boundary='hcdecor_'.wp_generate_password(18,false,false);
    $meta=['name'=>sanitize_file_name($name)];
    if(!$file_id && $parent_id) $meta['parents']=[(string)$parent_id];
    $body="--{$boundary}\r\nContent-Type: application/json; charset=UTF-8\r\n\r\n".
        wp_json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).
        "\r\n--{$boundary}\r\nContent-Type: {$mime}\r\n\r\n".$bytes."\r\n--{$boundary}--";
    if($file_id){
        $url='https://www.googleapis.com/upload/drive/v3/files/'.rawurlencode($file_id).'?uploadType=multipart&fields=id,name,mimeType,webViewLink,modifiedTime';
        $method='PATCH';
    }else{
        $url='https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,mimeType,webViewLink,modifiedTime';
        $method='POST';
    }
    $r=hcdecor_drive_request($method,$url,[
        'headers'=>['Content-Type'=>'multipart/related; boundary='.$boundary],
        'body'=>$body,
        'timeout'=>120
    ]);
    if(is_wp_error($r)) return $r;
    $d=json_decode($r['body'],true);
    return is_array($d)?$d:new WP_Error('drive_json','Upload response invalid.');
}

function hcdecor_drive_save_json($name,$data,$folder_key,$existing_file_id=''){
    $folders=hcdecor_drive_folders();
    $folder=(string)($folders[$folder_key]??'');
    if(!$folder) return new WP_Error('folder','Drive folder not configured: '.$folder_key);
    $bytes=wp_json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    return hcdecor_drive_multipart($existing_file_id,$name,'application/json',$bytes,$folder);
}

function hcdecor_drive_upload_attachment($attachment_id,$folder_key='media_input'){
    $attachment_id=(int)$attachment_id;
    if(get_post_type($attachment_id)!=='attachment') return new WP_Error('media','Invalid attachment.');
    $existing=(string)get_post_meta($attachment_id,'hc_drive_file_id',true);
    $path=get_attached_file($attachment_id);
    if(!$path||!is_readable($path)) return new WP_Error('file','Attachment file unavailable.');
    $bytes=file_get_contents($path);
    if($bytes===false) return new WP_Error('file','Cannot read attachment.');
    $folders=hcdecor_drive_folders();
    $folder=(string)($folders[$folder_key]??'');
    $name=basename($path);
    $mime=(string)(get_post_mime_type($attachment_id)?:'application/octet-stream');
    $res=hcdecor_drive_multipart($existing,$name,$mime,$bytes,$folder);
    if(!is_wp_error($res)&&!empty($res['id'])){
        update_post_meta($attachment_id,'hc_drive_file_id',sanitize_text_field($res['id']));
        update_post_meta($attachment_id,'hc_drive_url',esc_url_raw($res['webViewLink']??''));
        update_post_meta($attachment_id,'hc_drive_synced_at',current_time('mysql'));
    }
    return $res;
}

function hcdecor_drive_job_data($job_id){
    $job=get_post($job_id);
    if(!$job||$job->post_type!=='hc_content_job') return new WP_Error('job','Invalid job.');
    $project=(int)get_post_meta($job_id,'hc_project_id',true);
    $media_ids=array_values(array_filter(array_map('intval',(array)get_post_meta($job_id,'hc_media_ids',true))));
    $media=[];
    foreach($media_ids as $mid){
        $media[]=[
            'wp_id'=>$mid,
            'drive_file_id'=>(string)get_post_meta($mid,'hc_drive_file_id',true),
            'drive_url'=>(string)get_post_meta($mid,'hc_drive_url',true),
            'title'=>get_the_title($mid),
            'mime'=>(string)get_post_mime_type($mid),
            'ai_summary'=>(string)get_post_meta($mid,'hc_ai_summary',true),
            'ai_tags'=>(array)get_post_meta($mid,'hc_ai_tags',true),
            'cover_score'=>(int)get_post_meta($mid,'hc_ai_cover_score',true)
        ];
    }
    $fields=[];
    if(function_exists('hcdecor_ops_fields')){
        foreach(hcdecor_ops_fields() as $k) $fields[$k]=get_post_meta($job_id,'hc_'.$k,true);
    }
    return [
        'schema'=>'hcdecor.content-job.v1',
        'saved_at'=>current_time('mysql'),
        'source_job_id'=>(int)$job_id,
        'project'=>[
            'wp_id'=>$project,
            'title'=>$project?get_the_title($project):'',
            'url'=>$project?get_permalink($project):''
        ],
        'job'=>[
            'title'=>$job->post_title,
            'brief'=>$job->post_content,
            'status'=>(string)get_post_meta($job_id,'hc_agent_status',true),
            'channels'=>(array)get_post_meta($job_id,'hc_channels',true),
            'cover_id'=>(int)get_post_meta($job_id,'hc_cover_id',true),
            'ai_provider'=>(string)get_post_meta($job_id,'hc_ai_provider',true),
            'ai_model'=>(string)get_post_meta($job_id,'hc_ai_model',true)
        ],
        'content'=>$fields,
        'media'=>$media,
        'workflow_log'=>(array)get_post_meta($job_id,'hc_workflow_log',true)
    ];
}

function hcdecor_drive_stage_folder_key($status){
    $map=[
        'draft'=>'content_draft','processing'=>'content_draft','failed'=>'content_draft',
        'review'=>'content_review','approved'=>'content_approved','published_web'=>'content_published'
    ];
    return $map[$status]??'content_draft';
}

function hcdecor_drive_save_job($job_id,$sync_media=true){
    $data=hcdecor_drive_job_data($job_id);
    if(is_wp_error($data)) return $data;
    if($sync_media){
        foreach((array)get_post_meta($job_id,'hc_media_ids',true) as $mid){
            $mid=(int)$mid;
            if($mid && !(string)get_post_meta($mid,'hc_drive_file_id',true)) hcdecor_drive_upload_attachment($mid,'media_input');
        }
        $data=hcdecor_drive_job_data($job_id);
    }
    $status=(string)get_post_meta($job_id,'hc_agent_status',true);
    $folder_key=hcdecor_drive_stage_folder_key($status);
    $slug=sanitize_file_name('JOB-'.$job_id.'-'.($data['project']['title']?:'content').'.json');
    $meta_key='hc_drive_job_file_'.$folder_key;
    $existing=(string)get_post_meta($job_id,$meta_key,true);
    $res=hcdecor_drive_save_json($slug,$data,$folder_key,$existing);
    if(!is_wp_error($res)&&!empty($res['id'])){
        update_post_meta($job_id,$meta_key,sanitize_text_field($res['id']));
        update_post_meta($job_id,'hc_drive_job_file_id',sanitize_text_field($res['id']));
        update_post_meta($job_id,'hc_drive_job_url',esc_url_raw($res['webViewLink']??''));
        update_post_meta($job_id,'hc_drive_synced_at',current_time('mysql'));
        delete_post_meta($job_id,'hc_drive_error');
    }elseif(is_wp_error($res)){
        update_post_meta($job_id,'hc_drive_error',$res->get_error_message());
    }
    return $res;
}

function hcdecor_drive_download($file_id){
    $file_id=preg_replace('/[^A-Za-z0-9_-]/','',(string)$file_id);
    if(!$file_id) return new WP_Error('file','Invalid Drive file ID.');
    $r=hcdecor_drive_request('GET','https://www.googleapis.com/drive/v3/files/'.rawurlencode($file_id).'?alt=media');
    return is_wp_error($r)?$r:$r['body'];
}

function hcdecor_drive_import_job($file_id){
    $body=hcdecor_drive_download($file_id);
    if(is_wp_error($body)) return $body;
    $d=json_decode($body,true);
    if(!is_array($d)||($d['schema']??'')!=='hcdecor.content-job.v1') return new WP_Error('schema','Invalid HCDecor content job file.');
    $source=(int)($d['source_job_id']??0);
    $id=$source&&get_post_type($source)==='hc_content_job'?$source:0;
    $post=[
        'post_type'=>'hc_content_job','post_status'=>'publish',
        'post_title'=>sanitize_text_field($d['job']['title']??'Imported Drive Job'),
        'post_content'=>sanitize_textarea_field($d['job']['brief']??'')
    ];
    if($id){$post['ID']=$id;$r=wp_update_post($post,true);}else{$r=wp_insert_post($post,true);}
    if(is_wp_error($r)) return $r;
    $id=(int)$r;
    if(!empty($d['project']['wp_id'])) update_post_meta($id,'hc_project_id',(int)$d['project']['wp_id']);
    update_post_meta($id,'hc_agent_status',sanitize_key($d['job']['status']??'draft'));
    update_post_meta($id,'hc_channels',(array)($d['job']['channels']??[]));
    if(function_exists('hcdecor_ops_save_fields')) hcdecor_ops_save_fields($id,(array)($d['content']??[]));
    update_post_meta($id,'hc_drive_job_file_id',$file_id);
    update_post_meta($id,'hc_drive_loaded_at',current_time('mysql'));
    return $id;
}

function hcdecor_drive_save_prompt($title,$prompt,$existing=''){
    $data=[
        'schema'=>'hcdecor.prompt.v1',
        'title'=>sanitize_text_field($title),
        'prompt'=>wp_kses_post($prompt),
        'saved_at'=>current_time('mysql')
    ];
    return hcdecor_drive_save_json(sanitize_file_name($title?:'Prompt').'.json',$data,'prompts',$existing);
}

add_action('hcdecor_workflow_status_changed',function($job_id,$old,$status){
    if(!hcdecor_drive_configured()) return;
    if(in_array($status,['review','approved','published_web'],true)) hcdecor_drive_save_job((int)$job_id,$status==='review');
},20,3);

add_action('hcdecor_after_ai_content_generated',function($job_id){
    if(hcdecor_drive_configured()) hcdecor_drive_save_job((int)$job_id,true);
},20,1);

add_action('hcdecor_after_web_publish',function($job_id,$project_id){
    if(!hcdecor_drive_configured()) return;
    hcdecor_drive_save_job((int)$job_id,false);
    hcdecor_drive_save_json('PUBLISH-'.$project_id.'-'.gmdate('Ymd-His').'.json',[
        'schema'=>'hcdecor.publish.v1','job_id'=>(int)$job_id,'project_id'=>(int)$project_id,
        'url'=>get_permalink($project_id),'published_at'=>current_time('mysql')
    ],'manifests');
},30,2);

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','Drive Vault','Drive Vault','manage_options','hcdecor-drive-vault','hcdecor_drive_vault_page',6);
},29);


add_action('admin_post_hcdecor_drive_save_connect',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_drive_save_connect');
    $client_id=trim((string)wp_unslash($_POST['client_id']??''));
    $client_secret=trim((string)wp_unslash($_POST['client_secret']??''));
    if($client_id!=='') update_option('hcdecor_drive_client_id',$client_id,false);
    if($client_secret!=='') update_option('hcdecor_drive_client_secret',$client_secret,false);
    if(hcdecor_drive_secret('client_id')==='' || hcdecor_drive_secret('client_secret')===''){
        wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&oauth_missing=1')); exit;
    }
    wp_redirect(hcdecor_drive_oauth_connect_url()); exit;
});

add_action('admin_post_hcdecor_drive_settings',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_drive_settings');
    foreach(['client_id','client_secret','refresh_token'] as $k){
        $v=trim((string)wp_unslash($_POST[$k]??''));
        if($v!=='') update_option('hcdecor_drive_'.$k,$v,false);
    }
    if(!empty($_POST['clear_auth'])){
        foreach(['client_id','client_secret','refresh_token'] as $k) delete_option('hcdecor_drive_'.$k);
        delete_transient('hcdecor_drive_access_token');
    }
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&saved=1'));exit;
});

add_action('admin_post_hcdecor_drive_test',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_drive_test');
    $r=hcdecor_drive_test();
    if(is_wp_error($r)){
        update_option('hcdecor_drive_test_status','error',false);
        update_option('hcdecor_drive_test_message',$r->get_error_message(),false);
    }else{
        update_option('hcdecor_drive_test_status','ok',false);
        update_option('hcdecor_drive_test_message',(string)($r['user']['emailAddress']??'Connected'),false);
    }
    update_option('hcdecor_drive_tested_at',time(),false);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&tested=1'));exit;
});

add_action('admin_post_hcdecor_drive_save_job',function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    $id=(int)($_POST['job_id']??0);check_admin_referer('hcdecor_drive_save_job_'.$id);
    $r=hcdecor_drive_save_job($id,true);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&job='.$id.(is_wp_error($r)?'&drive_error=1':'&drive_saved=1')));exit;
});

add_action('admin_post_hcdecor_drive_import_job',function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    check_admin_referer('hcdecor_drive_import_job');
    $file=sanitize_text_field(wp_unslash($_POST['file_id']??''));
    $r=hcdecor_drive_import_job($file);
    if(is_wp_error($r)) wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&import_error=1'));
    else wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.(int)$r.'&drive_loaded=1'));
    exit;
});

add_action('admin_post_hcdecor_drive_save_prompt',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_drive_save_prompt');
    $title=sanitize_text_field(wp_unslash($_POST['prompt_title']??''));
    $prompt=wp_unslash($_POST['prompt']??'');
    $r=hcdecor_drive_save_prompt($title,$prompt);
    if(!is_wp_error($r)){
        update_option('hcdecor_drive_active_prompt',wp_kses_post($prompt),false);
        update_option('hcdecor_drive_active_prompt_file',sanitize_text_field($r['id']??''),false);
    }
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-drive-vault&prompt_saved='.(is_wp_error($r)?'0':'1')));exit;
});

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/drive/status',[
        'methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(){
            $test=hcdecor_drive_configured()?hcdecor_drive_test():new WP_Error('auth','Not configured');
            return rest_ensure_response([
                'configured'=>hcdecor_drive_configured(),
                'connected'=>!is_wp_error($test),
                'folders'=>hcdecor_drive_folders(),
                'error'=>is_wp_error($test)?$test->get_error_message():''
            ]);
        }
    ]);
    register_rest_route('hcdecor/v1','/drive/list/(?P<folder>[A-Za-z0-9_-]+)',[
        'methods'=>'GET','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $folder=(string)$r['folder'];$folders=hcdecor_drive_folders();
            $id=$folders[$folder]??$folder;
            $res=hcdecor_drive_list($id,(int)($r->get_param('limit')?:100));
            return is_wp_error($res)?$res:rest_ensure_response($res);
        }
    ]);
    register_rest_route('hcdecor/v1','/drive/jobs/(?P<id>\d+)/save',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $res=hcdecor_drive_save_job((int)$r['id'],true);
            return is_wp_error($res)?$res:rest_ensure_response($res);
        }
    ]);
    register_rest_route('hcdecor/v1','/drive/import',[
        'methods'=>'POST','permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $res=hcdecor_drive_import_job((string)$r->get_param('file_id'));
            return is_wp_error($res)?$res:rest_ensure_response(['job_id'=>$res]);
        }
    ]);
});

function hcdecor_drive_vault_page(){
    if(!current_user_can('manage_options')) return;
    $folders=hcdecor_drive_folders();
    $status=(string)get_option('hcdecor_drive_test_status','');
    $msg=(string)get_option('hcdecor_drive_test_message','');
    $jobs=get_posts(['post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>20,'orderby'=>'modified','order'=>'DESC']);
    $prompt_files=hcdecor_drive_configured()?hcdecor_drive_list($folders['prompts'],20):[];
    if(is_wp_error($prompt_files)) $prompt_files=[];
    ?>
    <div class="wrap hcdv" style="max-width:1400px">
      <h1>HCDecor Drive Vault</h1>
      <p><strong>Google Drive = DATA Vault</strong> · WordPress = Index / Queue / Workflow.</p>
      <div style="display:grid;grid-template-columns:420px 1fr;gap:14px">
        <section style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:18px">
          <h2>Google Drive Connection</h2>
          <p><strong><?php echo hcdecor_drive_configured()?($status==='ok'?'CONNECTED':'CONFIGURED'):'NOT CONNECTED';?></strong><?php if($msg):?> · <?php echo esc_html($msg);?><?php endif;?></p>

          <div style="background:#f6f7f7;border:1px solid #dcdcde;border-radius:10px;padding:12px;margin:12px 0">
            <strong>Bước 1 · Google OAuth</strong>
            <p style="margin:6px 0 0">Tạo OAuth Client kiểu <strong>Web application</strong> và thêm Redirect URI này:</p>
            <code style="display:block;word-break:break-all;margin-top:6px"><?php echo esc_html(hcdecor_drive_oauth_redirect_uri());?></code>
          </div>

          <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
            <input type="hidden" name="action" value="hcdecor_drive_save_connect"><?php wp_nonce_field('hcdecor_drive_save_connect');?>
            <p><label><strong>OAuth Client ID</strong></label><input class="widefat" type="password" name="client_id" autocomplete="new-password" placeholder="<?php echo hcdecor_drive_secret('client_id')?'Đã lưu · nhập mới để thay':'Dán Client ID';?>"></p>
            <p><label><strong>OAuth Client Secret</strong></label><input class="widefat" type="password" name="client_secret" autocomplete="new-password" placeholder="<?php echo hcdecor_drive_secret('client_secret')?'Đã lưu · nhập mới để thay':'Dán Client Secret';?>"></p>
            <p><button class="button button-primary">Lưu & Connect Google Drive</button></p>
          </form>

          <?php if(hcdecor_drive_secret('refresh_token')!==''):?>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
              <input type="hidden" name="action" value="hcdecor_drive_test"><?php wp_nonce_field('hcdecor_drive_test');?>
              <button class="button">Test Drive</button>
            </form>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
              <input type="hidden" name="action" value="hcdecor_drive_disconnect"><?php wp_nonce_field('hcdecor_drive_disconnect');?>
              <button class="button">Disconnect</button>
            </form>
          </div>
          <?php endif;?>

          <details style="margin-top:12px"><summary>Advanced / Manual token</summary>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" style="margin-top:10px">
              <input type="hidden" name="action" value="hcdecor_drive_settings"><?php wp_nonce_field('hcdecor_drive_settings');?>
              <p><label>Refresh Token</label><input class="widefat" type="password" name="refresh_token" autocomplete="new-password" placeholder="<?php echo hcdecor_drive_secret('refresh_token')?'Đã lưu · nhập mới để thay':'Manual only';?>"></p>
              <p><button class="button">Lưu manual token</button> <label><input type="checkbox" name="clear_auth" value="1"> Xóa auth</label></p>
            </form>
          </details>

          <?php $connected_email=(string)get_option('hcdecor_drive_connected_email',''); if($connected_email):?><p><strong>Google account:</strong> <?php echo esc_html($connected_email);?></p><?php endif;?>
          <hr><p><strong>Root Vault</strong><br><code><?php echo esc_html($folders['root']);?></code></p>
          <p><a class="button" target="_blank" rel="noopener" href="<?php echo esc_url('https://drive.google.com/drive/folders/'.$folders['root']);?>">Open Google Drive Vault</a></p>
        </section>
        <section>
          <div style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:18px;margin-bottom:14px">
            <h2>Prompt → Media → Content → Review → Publish → Archive</h2>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
            <?php foreach(['prompts'=>'Prompt Vault','media_input'=>'Media Input','content_review'=>'Review','content_approved'=>'Approved','content_published'=>'Published','archive'=>'Archive'] as $k=>$label):?>
              <a class="button" target="_blank" rel="noopener" href="<?php echo esc_url('https://drive.google.com/drive/folders/'.$folders[$k]);?>"><?php echo esc_html($label);?></a>
            <?php endforeach;?>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:18px">
              <h2>Prompt Vault</h2>
              <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
                <input type="hidden" name="action" value="hcdecor_drive_save_prompt"><?php wp_nonce_field('hcdecor_drive_save_prompt');?>
                <p><input class="widefat" type="text" name="prompt_title" placeholder="Tên prompt" required></p>
                <p><textarea class="widefat" name="prompt" rows="8" placeholder="Prompt / SOP / instruction..." required><?php echo esc_textarea(get_option('hcdecor_drive_active_prompt',''));?></textarea></p>
                <button class="button button-primary">Save Prompt → Drive</button>
              </form>
              <?php if($prompt_files):?><hr><strong>Drive Prompts</strong><ul><?php foreach($prompt_files as $pf):?><li><code><?php echo esc_html($pf['id']);?></code> · <?php echo esc_html($pf['name']);?></li><?php endforeach;?></ul><?php endif;?>
            </div>
            <div style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:18px">
              <h2>Load Content Job</h2>
              <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>">
                <input type="hidden" name="action" value="hcdecor_drive_import_job"><?php wp_nonce_field('hcdecor_drive_import_job');?>
                <p><input class="widefat" type="text" name="file_id" placeholder="Google Drive JSON File ID" required></p>
                <button class="button">Load Drive → HUB</button>
              </form>
              <hr><h3>Recent Jobs</h3>
              <?php foreach($jobs as $j):?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" style="display:flex;justify-content:space-between;gap:8px;border-top:1px solid #eee;padding:8px 0">
                  <span>#<?php echo $j->ID;?> <?php echo esc_html($j->post_title);?></span>
                  <input type="hidden" name="action" value="hcdecor_drive_save_job"><input type="hidden" name="job_id" value="<?php echo $j->ID;?>"><?php wp_nonce_field('hcdecor_drive_save_job_'.$j->ID);?>
                  <button class="button">Save → Drive</button>
                </form>
              <?php endforeach;?>
            </div>
          </div>
        </section>
      </div>
    </div><?php
}
