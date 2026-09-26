<?php
if (!defined('ABSPATH')) exit;

/**
 * HCDecor AI Providers
 * Production text + vision generation for Content Operations.
 * Secrets stay in wp-config.php constants or WordPress options, never in Git.
 */

function hcdecor_ai_secret($provider){
    if($provider==='openai' && defined('HCDECOR_OPENAI_API_KEY')) return trim((string)HCDECOR_OPENAI_API_KEY);
    if($provider==='gemini' && defined('HCDECOR_GEMINI_API_KEY')) return trim((string)HCDECOR_GEMINI_API_KEY);
    return trim((string)get_option('hcdecor_ai_'.$provider.'_key',''));
}
function hcdecor_ai_model($provider){
    if($provider==='openai'){
        if(defined('HCDECOR_OPENAI_MODEL') && HCDECOR_OPENAI_MODEL) return (string)HCDECOR_OPENAI_MODEL;
        return (string)get_option('hcdecor_ai_openai_model','gpt-5.6-terra');
    }
    if($provider==='gemini'){
        if(defined('HCDECOR_GEMINI_MODEL') && HCDECOR_GEMINI_MODEL) return (string)HCDECOR_GEMINI_MODEL;
        return (string)get_option('hcdecor_ai_gemini_model','gemini-3.8-flash');
    }
    return '';
}
function hcdecor_ai_primary(){
    $p=(string)get_option('hcdecor_ai_primary','auto');
    return in_array($p,['auto','openai','gemini'],true)?$p:'auto';
}
function hcdecor_ai_available($provider){
    return hcdecor_ai_secret($provider)!=='';
}
function hcdecor_ai_provider_order(){
    $primary=hcdecor_ai_primary();
    if($primary==='openai') return ['openai','gemini'];
    if($primary==='gemini') return ['gemini','openai'];
    return ['openai','gemini'];
}

function hcdecor_ai_schema(){
    return [
        'type'=>'object',
        'properties'=>[
            'web_title'=>['type'=>'string'],
            'web_intro'=>['type'=>'string'],
            'web_body'=>['type'=>'string'],
            'seo_meta'=>['type'=>'string'],
            'facebook_caption'=>['type'=>'string'],
            'tiktok_script'=>['type'=>'string'],
            'youtube_title'=>['type'=>'string'],
            'youtube_description'=>['type'=>'string']
        ],
        'required'=>['web_title','web_intro','web_body','seo_meta','facebook_caption','tiktok_script','youtube_title','youtube_description'],
        'additionalProperties'=>false
    ];
}

function hcdecor_ai_prompt($job_id){
    $job=get_post($job_id);
    if(!$job || $job->post_type!=='hc_content_job') return '';
    $project_id=(int)get_post_meta($job_id,'hc_project_id',true);
    $project=$project_id?get_post($project_id):null;
    $channels=(array)get_post_meta($job_id,'hc_channels',true);
    $review_note=(string)get_post_meta($job_id,'hc_review_note',true);

    $parts=[
        'Bạn là HCDecor HUB Agent phụ trách nội dung dự án thiết kế, thi công, nội thất, kiến trúc, bảng hiệu và 3D.',
        'Chỉ mô tả những gì có cơ sở từ brief, dữ liệu Project và hình ảnh. Không bịa tên khách hàng, địa điểm, vật liệu, kích thước, chi phí hoặc tình trạng thi công nếu không có dữ liệu.',
        'Ngôn ngữ mặc định: tiếng Việt tự nhiên, súc tích, chuyên nghiệp, ưu tiên hình ảnh và trải nghiệm không gian.',
        'Hãy trả về JSON hợp lệ, không markdown, đúng các trường được yêu cầu.',
        'PROJECT: '.($project?$project->post_title:''),
        'PROJECT EXCERPT: '.($project?$project->post_excerpt:''),
        'PROJECT CONTENT: '.($project?wp_strip_all_tags($project->post_content):''),
        'BRIEF: '.$job->post_content,
        'CHANNELS: '.implode(', ',$channels?:['web']),
    ];
    $media_notes=[];
    foreach((array)get_post_meta($job_id,'hc_media_ids',true) as $mid){
        $mid=(int)$mid; $summary=(string)get_post_meta($mid,'hc_ai_summary',true);
        if($summary==='') continue;
        $media_notes[]='#'.$mid.' '.$summary.' | cover_score='.(int)get_post_meta($mid,'hc_ai_cover_score',true).' | tags='.implode(',',(array)get_post_meta($mid,'hc_ai_tags',true));
    }
    if($media_notes) $parts[]='MEDIA ANALYSIS:\n'.implode("\n",$media_notes);
    $drive_prompt=(string)get_option('hcdecor_drive_active_prompt','');
    if($drive_prompt!=='') $parts[]='ACTIVE DRIVE PROMPT:\n'.wp_strip_all_tags($drive_prompt);
    if($review_note!=='') $parts[]='REVIEW NOTE: '.$review_note;
    $parts[]='Yêu cầu output: web_title, web_intro, web_body, seo_meta, facebook_caption, tiktok_script, youtube_title, youtube_description.';
    $parts[]='TikTok script nên có Hook → cảnh/shot gợi ý → nội dung chính → CTA. SEO meta ngắn gọn. Không thêm hashtag quá mức.';
    return implode("\n\n",$parts);
}

function hcdecor_ai_media_parts($job_id,$provider){
    $ids=(array)get_post_meta($job_id,'hc_media_ids',true);
    $ids=array_slice(array_values(array_filter(array_map('intval',$ids))),0,4);
    $parts=[]; $bytes_total=0; $limit=12*1024*1024;
    foreach($ids as $id){
        if(!wp_attachment_is_image($id)) continue;
        $path=get_attached_file($id);
        if(!$path || !is_readable($path)) continue;
        $size=(int)filesize($path);
        if($size<=0 || $size>6*1024*1024 || ($bytes_total+$size)>$limit) continue;
        $raw=file_get_contents($path);
        if($raw===false) continue;
        $mime=(string)get_post_mime_type($id);
        if(!in_array($mime,['image/jpeg','image/png','image/webp','image/gif'],true)) continue;
        $b64=base64_encode($raw); $bytes_total+=$size;
        if($provider==='openai'){
            $parts[]=['type'=>'input_image','image_url'=>'data:'.$mime.';base64,'.$b64,'detail'=>'auto'];
        }else{
            $parts[]=['type'=>'image','data'=>$b64,'mime_type'=>$mime];
        }
    }
    return $parts;
}

function hcdecor_ai_extract_openai_text($data){
    if(!empty($data['output_text']) && is_string($data['output_text'])) return $data['output_text'];
    foreach((array)($data['output']??[]) as $item){
        if(($item['type']??'')!=='message') continue;
        foreach((array)($item['content']??[]) as $part){
            if(isset($part['text']) && is_string($part['text'])) return $part['text'];
        }
    }
    return '';
}
function hcdecor_ai_extract_gemini_text($data){
    if(!empty($data['output_text']) && is_string($data['output_text'])) return $data['output_text'];
    foreach((array)($data['steps']??[]) as $step){
        if(($step['type']??'')!=='model_output') continue;
        foreach((array)($step['content']??[]) as $part){
            if(($part['type']??'')==='text' && isset($part['text'])) return (string)$part['text'];
        }
    }
    return '';
}

function hcdecor_ai_call_openai($job_id){
    $key=hcdecor_ai_secret('openai');
    if($key==='') return new WP_Error('no_openai_key','OpenAI API key chưa cấu hình.');
    $content=[['type'=>'input_text','text'=>hcdecor_ai_prompt($job_id)]];
    $content=array_merge($content,hcdecor_ai_media_parts($job_id,'openai'));
    $payload=[
        'model'=>hcdecor_ai_model('openai'),
        'store'=>false,
        'input'=>[['role'=>'user','content'=>$content]],
        'text'=>['format'=>['type'=>'json_schema','name'=>'hcdecor_content','strict'=>true,'schema'=>hcdecor_ai_schema()]]
    ];
    $r=wp_remote_post('https://api.openai.com/v1/responses',[
        'timeout'=>90,
        'headers'=>['Authorization'=>'Bearer '.$key,'Content-Type'=>'application/json'],
        'body'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
    ]);
    if(is_wp_error($r)) return $r;
    $status=wp_remote_retrieve_response_code($r);
    $data=json_decode(wp_remote_retrieve_body($r),true);
    if($status<200 || $status>=300){
        $msg=(string)($data['error']['message']??('OpenAI HTTP '.$status));
        return new WP_Error('openai_api',$msg,['status'=>$status]);
    }
    $text=hcdecor_ai_extract_openai_text((array)$data);
    $json=json_decode($text,true);
    if(!is_array($json)) return new WP_Error('openai_json','OpenAI trả về dữ liệu không hợp lệ.');
    return ['provider'=>'openai','model'=>(string)($data['model']??hcdecor_ai_model('openai')),'content'=>$json,'usage'=>$data['usage']??[]];
}

function hcdecor_ai_call_gemini($job_id){
    $key=hcdecor_ai_secret('gemini');
    if($key==='') return new WP_Error('no_gemini_key','Gemini API key chưa cấu hình.');
    $input=[['type'=>'text','text'=>hcdecor_ai_prompt($job_id)]];
    $input=array_merge($input,hcdecor_ai_media_parts($job_id,'gemini'));
    $payload=[
        'model'=>hcdecor_ai_model('gemini'),
        'store'=>false,
        'input'=>$input,
        'response_format'=>[
            'type'=>'text',
            'mime_type'=>'application/json',
            'schema'=>hcdecor_ai_schema()
        ]
    ];
    $r=wp_remote_post('https://generativelanguage.googleapis.com/v1beta/interactions',[
        'timeout'=>90,
        'headers'=>['x-goog-api-key'=>$key,'Content-Type'=>'application/json','Api-Revision'=>'2026-05-20'],
        'body'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
    ]);
    if(is_wp_error($r)) return $r;
    $status=wp_remote_retrieve_response_code($r);
    $data=json_decode(wp_remote_retrieve_body($r),true);
    if($status<200 || $status>=300){
        $msg=(string)($data['error']['message']??('Gemini HTTP '.$status));
        return new WP_Error('gemini_api',$msg,['status'=>$status]);
    }
    $text=hcdecor_ai_extract_gemini_text((array)$data);
    $json=json_decode($text,true);
    if(!is_array($json)) return new WP_Error('gemini_json','Gemini trả về dữ liệu không hợp lệ.');
    return ['provider'=>'gemini','model'=>(string)($data['model']??hcdecor_ai_model('gemini')),'content'=>$json,'usage'=>$data['usage']??[]];
}


function hcdecor_ai_test_provider($provider){
    if(!in_array($provider,['openai','gemini'],true)) return new WP_Error('provider','Invalid provider.');
    $key=hcdecor_ai_secret($provider);
    if($key==='') return new WP_Error('key','API key chưa cấu hình.');
    $model=hcdecor_ai_model($provider);
    if($provider==='openai'){
        $url='https://api.openai.com/v1/models/'.rawurlencode($model);
        $r=wp_remote_get($url,['timeout'=>20,'headers'=>['Authorization'=>'Bearer '.$key]]);
    }else{
        $url='https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model);
        $r=wp_remote_get($url,['timeout'=>20,'headers'=>['x-goog-api-key'=>$key]]);
    }
    if(is_wp_error($r)) return $r;
    $status=(int)wp_remote_retrieve_response_code($r);
    $data=json_decode(wp_remote_retrieve_body($r),true);
    if($status<200 || $status>=300){
        $msg=(string)($data['error']['message']??('HTTP '.$status));
        return new WP_Error('api_test',$msg,['status'=>$status]);
    }
    return ['provider'=>$provider,'model'=>$model,'ok'=>true];
}
function hcdecor_ai_store_test($provider,$result){
    if(is_wp_error($result)){
        update_option('hcdecor_ai_'.$provider.'_test_status','error',false);
        update_option('hcdecor_ai_'.$provider.'_test_message',sanitize_text_field($result->get_error_message()),false);
    }else{
        update_option('hcdecor_ai_'.$provider.'_test_status','ok',false);
        update_option('hcdecor_ai_'.$provider.'_test_message','',false);
    }
    update_option('hcdecor_ai_'.$provider.'_tested_at',time(),false);
}

function hcdecor_ai_generate_job($job_id){
    if(get_post_type($job_id)!=='hc_content_job') return new WP_Error('invalid_job','Invalid content job.');
    $errors=[];
    foreach(hcdecor_ai_provider_order() as $provider){
        if(!hcdecor_ai_available($provider)) continue;
        $result=$provider==='openai'?hcdecor_ai_call_openai($job_id):hcdecor_ai_call_gemini($job_id);
        if(is_wp_error($result)){
            $errors[$provider]=$result->get_error_message();
            continue;
        }
        if(function_exists('hcdecor_ops_save_fields')) hcdecor_ops_save_fields($job_id,$result['content']);
        update_post_meta($job_id,'hc_ai_provider',$result['provider']);
        update_post_meta($job_id,'hc_ai_model',$result['model']);
        update_post_meta($job_id,'hc_ai_usage',$result['usage']);
        update_post_meta($job_id,'hc_ai_generated_at',current_time('mysql'));
        delete_post_meta($job_id,'hc_ai_error');
        if(function_exists('hcdecor_workflow_set_status')) hcdecor_workflow_set_status($job_id,'review','AI generation completed via '.$result['provider']);
        else update_post_meta($job_id,'hc_agent_status','review');
        delete_post_meta($job_id,'hc_agent_lock_until');
        do_action('hcdecor_after_ai_content_generated',$job_id,$result['provider'],$result['model']);
        return $result;
    }
    $message=$errors?implode(' | ',array_map(function($k,$v){return $k.': '.$v;},array_keys($errors),$errors)):'Chưa có AI API key.';
    update_post_meta($job_id,'hc_ai_error',$message);
    if(function_exists('hcdecor_workflow_set_status')) hcdecor_workflow_set_status($job_id,'failed',$message);
    else update_post_meta($job_id,'hc_agent_status','failed');
    delete_post_meta($job_id,'hc_agent_lock_until');
    return new WP_Error('ai_failed',$message);
}

add_filter('cron_schedules',function($s){
    $s['hcdecor_1min']=['interval'=>60,'display'=>'HCDecor every minute'];
    return $s;
});
add_action('init',function(){
    if(!wp_next_scheduled('hcdecor_ai_worker_tick')) wp_schedule_event(time()+30,'hcdecor_1min','hcdecor_ai_worker_tick');
},40);
add_action('hcdecor_ai_worker_tick',function(){
    if(!hcdecor_ai_available('openai') && !hcdecor_ai_available('gemini')) return;
    $jobs=get_posts([
        'post_type'=>'hc_content_job','post_status'=>'publish','numberposts'=>1,'orderby'=>'date','order'=>'ASC',
        'meta_query'=>[['key'=>'hc_agent_status','value'=>'draft']]
    ]);
    if(!$jobs) return;
    $id=(int)$jobs[0]->ID;
    $lock=(int)get_post_meta($id,'hc_agent_lock_until',true);
    if($lock>time()) return;
    update_post_meta($id,'hc_agent_lock_until',time()+180);
    if(function_exists('hcdecor_workflow_set_status')) hcdecor_workflow_set_status($id,'processing','Internal AI worker');
    else update_post_meta($id,'hc_agent_status','processing');
    hcdecor_ai_generate_job($id);
});

add_action('admin_post_hcdecor_ai_run_job',function(){
    if(!current_user_can('edit_posts')) wp_die('Forbidden');
    $id=(int)($_POST['job_id']??0);
    check_admin_referer('hcdecor_ai_run_'.$id);
    if(get_post_type($id)!=='hc_content_job') wp_die('Invalid job');
    update_post_meta($id,'hc_agent_lock_until',time()+180);
    if(function_exists('hcdecor_workflow_set_status')) hcdecor_workflow_set_status($id,'processing','Manual AI run');
    $r=hcdecor_ai_generate_job($id);
    $q=is_wp_error($r)?'ai_error=1':'ai_done=1';
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-content-operations&job='.$id.'&'.$q)); exit;
});

add_action('admin_footer',function(){
    if(!isset($_GET['page']) || $_GET['page']!=='hcdecor-content-operations' || empty($_GET['job'])) return;
    $id=(int)$_GET['job']; if(get_post_type($id)!=='hc_content_job') return;
    $status=(string)get_post_meta($id,'hc_agent_status',true);
    if(!in_array($status,['draft','failed'],true)) return;
    ?>
    <script>
    (function(){
      const root=document.querySelector('.hcops'); if(!root)return;
      const cards=root.querySelectorAll('.hcops-card'); if(cards.length<2)return;
      const form=document.createElement('form');form.method='post';form.action='<?php echo esc_js(admin_url('admin-post.php'));?>';form.style.margin='12px 14px 16px';
      form.innerHTML='<input type="hidden" name="action" value="hcdecor_ai_run_job"><input type="hidden" name="job_id" value="<?php echo $id;?>"><input type="hidden" name="_wpnonce" value="<?php echo esc_js(wp_create_nonce('hcdecor_ai_run_'.$id));?>"><button class="button button-primary">Generate bằng AI</button>';
      cards[1].appendChild(form);
    })();
    </script><?php
});

add_action('admin_menu',function(){
    add_submenu_page('hcdecor-hub','AI Providers','AI Providers','manage_options','hcdecor-ai-providers','hcdecor_ai_settings_page',4);
},26);


add_action('admin_post_hcdecor_ai_test_provider',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    $provider=sanitize_key($_POST['provider']??'');
    check_admin_referer('hcdecor_ai_test_'.$provider);
    $result=hcdecor_ai_test_provider($provider);
    hcdecor_ai_store_test($provider,$result);
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-ai-providers&tested='.$provider)); exit;
});

add_action('admin_init',function(){
    if(!current_user_can('manage_options')) return;
    if(($_GET['page']??'')!=='hcdecor-ai-providers') return;
    foreach(['openai','gemini'] as $provider){
        if(!hcdecor_ai_available($provider)) continue;
        $last=(int)get_option('hcdecor_ai_'.$provider.'_tested_at',0);
        if((time()-$last)<21600) continue;
        hcdecor_ai_store_test($provider,hcdecor_ai_test_provider($provider));
    }
},20);

add_action('admin_post_hcdecor_ai_settings',function(){
    if(!current_user_can('manage_options')) wp_die('Forbidden');
    check_admin_referer('hcdecor_ai_settings');
    $primary=sanitize_key($_POST['primary']??'auto');
    if(!in_array($primary,['auto','openai','gemini'],true)) $primary='auto';
    update_option('hcdecor_ai_primary',$primary,false);
    update_option('hcdecor_ai_openai_model',sanitize_text_field(wp_unslash($_POST['openai_model']??'gpt-5.6-terra')),false);
    update_option('hcdecor_ai_gemini_model',sanitize_text_field(wp_unslash($_POST['gemini_model']??'gemini-3.8-flash')),false);
    foreach(['openai','gemini'] as $p){
        if(!empty($_POST[$p.'_clear'])) delete_option('hcdecor_ai_'.$p.'_key');
        $v=trim((string)wp_unslash($_POST[$p.'_key']??''));
        if($v!=='') update_option('hcdecor_ai_'.$p.'_key',$v,false);
    }
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-ai-providers&saved=1')); exit;
});

function hcdecor_ai_settings_page(){
    if(!current_user_can('manage_options')) return;
    $openai=hcdecor_ai_available('openai'); $gemini=hcdecor_ai_available('gemini');
    $openai_test=(string)get_option('hcdecor_ai_openai_test_status',''); $gemini_test=(string)get_option('hcdecor_ai_gemini_test_status','');
    $openai_msg=(string)get_option('hcdecor_ai_openai_test_message',''); $gemini_msg=(string)get_option('hcdecor_ai_gemini_test_message','');
    ?>
    <div class="wrap" style="max-width:900px"><h1>HCDecor AI Providers</h1>
    <p>AI xử lý nội dung + hình ảnh cho Content Operations. Có thể dùng một provider hoặc primary + fallback.</p>
    <?php if(isset($_GET['saved'])):?><div class="notice notice-success inline"><p>Đã lưu cấu hình AI.</p></div><?php endif;?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:20px">
      <input type="hidden" name="action" value="hcdecor_ai_settings"><?php wp_nonce_field('hcdecor_ai_settings');?>
      <table class="form-table"><tbody>
      <tr><th>Primary</th><td><select name="primary"><option value="auto" <?php selected(hcdecor_ai_primary(),'auto');?>>Auto fallback</option><option value="openai" <?php selected(hcdecor_ai_primary(),'openai');?>>OpenAI</option><option value="gemini" <?php selected(hcdecor_ai_primary(),'gemini');?>>Gemini</option></select></td></tr>
      <tr><th>OpenAI</th><td><p><strong><?php echo !$openai?'NOT CONFIGURED':($openai_test==='ok'?'CONNECTED':($openai_test==='error'?'ERROR':'KEY SAVED'));?></strong></p><input class="regular-text" type="password" name="openai_key" autocomplete="new-password" placeholder="Dán API key mới để lưu"><p><input class="regular-text" type="text" name="openai_model" value="<?php echo esc_attr(hcdecor_ai_model('openai'));?>"></p><label><input type="checkbox" name="openai_clear" value="1"> Xóa key lưu trong WordPress</label><?php if(defined('HCDECOR_OPENAI_API_KEY')):?><p><em>Key đang lấy từ wp-config.php.</em></p><?php endif;?><?php if($openai_test==='error'&&$openai_msg):?><p style="color:#b32d2e"><strong><?php echo esc_html($openai_msg);?></strong></p><?php endif;?><p><button class="button" form="hc-ai-test-openai">Test OpenAI</button></p></td></tr>
      <tr><th>Gemini</th><td><p><strong><?php echo !$gemini?'NOT CONFIGURED':($gemini_test==='ok'?'CONNECTED':($gemini_test==='error'?'ERROR':'KEY SAVED'));?></strong></p><input class="regular-text" type="password" name="gemini_key" autocomplete="new-password" placeholder="Dán API key mới để lưu"><p><input class="regular-text" type="text" name="gemini_model" value="<?php echo esc_attr(hcdecor_ai_model('gemini'));?>"></p><label><input type="checkbox" name="gemini_clear" value="1"> Xóa key lưu trong WordPress</label><?php if(defined('HCDECOR_GEMINI_API_KEY')):?><p><em>Key đang lấy từ wp-config.php.</em></p><?php endif;?><?php if($gemini_test==='error'&&$gemini_msg):?><p style="color:#b32d2e"><strong><?php echo esc_html($gemini_msg);?></strong></p><?php endif;?><p><button class="button" form="hc-ai-test-gemini">Test Gemini</button></p></td></tr>
      </tbody></table>
      <p><button class="button button-primary">Lưu AI Providers</button></p>
    </form>
    <p><strong>Worker:</strong> tự xử lý Content Job trạng thái Draft mỗi phút → Review. Social outbound vẫn OFF.</p>
    <form id="hc-ai-test-openai" method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" style="display:none"><input type="hidden" name="action" value="hcdecor_ai_test_provider"><input type="hidden" name="provider" value="openai"><?php wp_nonce_field('hcdecor_ai_test_openai');?></form>
    <form id="hc-ai-test-gemini" method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>" style="display:none"><input type="hidden" name="action" value="hcdecor_ai_test_provider"><input type="hidden" name="provider" value="gemini"><?php wp_nonce_field('hcdecor_ai_test_gemini');?></form>
    </div><?php
}
