<?php
if (!defined('ABSPATH')) exit;

/* HCDecor Media Intelligence: AI analysis, metadata suggestions, cover scoring. */

function hcdecor_media_ai_schema(){
    return [
        'type'=>'object',
        'properties'=>[
            'summary'=>['type'=>'string'],
            'alt'=>['type'=>'string'],
            'caption'=>['type'=>'string'],
            'tags'=>['type'=>'array','items'=>['type'=>'string']],
            'visual_type'=>['type'=>'string'],
            'cover_score'=>['type'=>'integer','minimum'=>0,'maximum'=>100]
        ],
        'required'=>['summary','alt','caption','tags','visual_type','cover_score'],
        'additionalProperties'=>false
    ];
}

function hcdecor_media_ai_data_url($attachment_id){
    if(!wp_attachment_is_image($attachment_id)) return new WP_Error('not_image','Only image attachments are supported.');
    $path=get_attached_file($attachment_id);
    if(!$path || !is_readable($path)) return new WP_Error('file','Image file unavailable.');
    $size=(int)filesize($path);
    if($size<=0 || $size>8*1024*1024) return new WP_Error('size','Image is too large for analysis.');
    $raw=file_get_contents($path);
    if($raw===false) return new WP_Error('read','Cannot read image.');
    $mime=(string)get_post_mime_type($attachment_id);
    if(!in_array($mime,['image/jpeg','image/png','image/webp','image/gif'],true)) return new WP_Error('mime','Unsupported image type.');
    return 'data:'.$mime.';base64,'.base64_encode($raw);
}

function hcdecor_media_ai_prompt($attachment_id){
    $title=get_the_title($attachment_id);
    $project_ids=get_posts([
        'post_type'=>'hc_project','post_status'=>['publish','draft'],'numberposts'=>20,'fields'=>'ids',
        'meta_query'=>[['key'=>'hc_project_gallery','value'=>'"'.(int)$attachment_id.'"','compare'=>'LIKE']]
    ]);
    $project_titles=array_map('get_the_title',$project_ids);
    return implode("\n",[
        'Phân tích hình ảnh này cho HCDecor HUB.',
        'Mục tiêu: quản lý media cho dự án thiết kế/thi công/nội thất/kiến trúc/bảng hiệu/3D.',
        'Không bịa thương hiệu, vật liệu, địa điểm hoặc trạng thái thi công nếu không nhìn thấy rõ.',
        'Viết tiếng Việt ngắn gọn, mô tả đúng hình.',
        'Chấm cover_score 0-100 theo độ rõ, bố cục, ánh sáng, khả năng dùng làm ảnh đại diện dự án.',
        'Title hiện tại: '.$title,
        'Project liên quan: '.implode(', ',$project_titles),
        'Trả JSON đúng schema.'
    ]);
}

function hcdecor_media_ai_call_openai($attachment_id){
    $key=function_exists('hcdecor_ai_secret')?hcdecor_ai_secret('openai'):'';
    if(!$key) return new WP_Error('key','OpenAI chưa cấu hình.');
    $img=hcdecor_media_ai_data_url($attachment_id); if(is_wp_error($img)) return $img;
    $payload=[
        'model'=>hcdecor_ai_model('openai'),
        'store'=>false,
        'input'=>[['role'=>'user','content'=>[
            ['type'=>'input_text','text'=>hcdecor_media_ai_prompt($attachment_id)],
            ['type'=>'input_image','image_url'=>$img,'detail'=>'auto']
        ]]],
        'text'=>['format'=>['type'=>'json_schema','name'=>'hcdecor_media_analysis','strict'=>true,'schema'=>hcdecor_media_ai_schema()]]
    ];
    $r=wp_remote_post('https://api.openai.com/v1/responses',[
        'timeout'=>90,'headers'=>['Authorization'=>'Bearer '.$key,'Content-Type'=>'application/json'],
        'body'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
    ]);
    if(is_wp_error($r)) return $r;
    $status=(int)wp_remote_retrieve_response_code($r); $data=json_decode(wp_remote_retrieve_body($r),true);
    if($status<200||$status>=300) return new WP_Error('openai',(string)($data['error']['message']??('OpenAI HTTP '.$status)));
    $text=function_exists('hcdecor_ai_extract_openai_text')?hcdecor_ai_extract_openai_text((array)$data):'';
    $json=json_decode($text,true);
    return is_array($json)?['provider'=>'openai','data'=>$json]:new WP_Error('json','OpenAI media JSON invalid.');
}

function hcdecor_media_ai_call_gemini($attachment_id){
    $key=function_exists('hcdecor_ai_secret')?hcdecor_ai_secret('gemini'):'';
    if(!$key) return new WP_Error('key','Gemini chưa cấu hình.');
    $url=hcdecor_media_ai_data_url($attachment_id); if(is_wp_error($url)) return $url;
    if(!preg_match('#^data:([^;]+);base64,(.+)$#s',$url,$m)) return new WP_Error('image','Invalid image encoding.');
    $payload=[
        'model'=>hcdecor_ai_model('gemini'),'store'=>false,
        'input'=>[
            ['type'=>'text','text'=>hcdecor_media_ai_prompt($attachment_id)],
            ['type'=>'image','data'=>$m[2],'mime_type'=>$m[1]]
        ],
        'response_format'=>['type'=>'text','mime_type'=>'application/json','schema'=>hcdecor_media_ai_schema()]
    ];
    $r=wp_remote_post('https://generativelanguage.googleapis.com/v1beta/interactions',[
        'timeout'=>90,
        'headers'=>['x-goog-api-key'=>$key,'Content-Type'=>'application/json','Api-Revision'=>'2026-05-20'],
        'body'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
    ]);
    if(is_wp_error($r)) return $r;
    $status=(int)wp_remote_retrieve_response_code($r); $data=json_decode(wp_remote_retrieve_body($r),true);
    if($status<200||$status>=300) return new WP_Error('gemini',(string)($data['error']['message']??('Gemini HTTP '.$status)));
    $text=function_exists('hcdecor_ai_extract_gemini_text')?hcdecor_ai_extract_gemini_text((array)$data):'';
    $json=json_decode($text,true);
    return is_array($json)?['provider'=>'gemini','data'=>$json]:new WP_Error('json','Gemini media JSON invalid.');
}

function hcdecor_media_ai_analyze($attachment_id){
    $attachment_id=(int)$attachment_id;
    if(get_post_type($attachment_id)!=='attachment') return new WP_Error('media','Invalid media.');
    $errors=[];
    $order=function_exists('hcdecor_ai_provider_order')?hcdecor_ai_provider_order():['openai','gemini'];
    foreach($order as $provider){
        $r=$provider==='openai'?hcdecor_media_ai_call_openai($attachment_id):hcdecor_media_ai_call_gemini($attachment_id);
        if(is_wp_error($r)){ $errors[]=$provider.': '.$r->get_error_message(); continue; }
        $d=$r['data'];
        update_post_meta($attachment_id,'hc_ai_summary',sanitize_textarea_field($d['summary']??''));
        update_post_meta($attachment_id,'hc_ai_alt',sanitize_text_field($d['alt']??''));
        update_post_meta($attachment_id,'hc_ai_caption',sanitize_textarea_field($d['caption']??''));
        update_post_meta($attachment_id,'hc_ai_tags',array_values(array_filter(array_map('sanitize_text_field',(array)($d['tags']??[])))));
        update_post_meta($attachment_id,'hc_ai_visual_type',sanitize_text_field($d['visual_type']??''));
        update_post_meta($attachment_id,'hc_ai_cover_score',max(0,min(100,(int)($d['cover_score']??0))));
        update_post_meta($attachment_id,'hc_ai_provider',sanitize_key($r['provider']));
        update_post_meta($attachment_id,'hc_ai_analyzed_at',current_time('mysql'));
        delete_post_meta($attachment_id,'hc_ai_error');
        return $r;
    }
    $msg=implode(' | ',$errors);
    update_post_meta($attachment_id,'hc_ai_error',$msg);
    return new WP_Error('ai_failed',$msg?:'No AI provider available.');
}

add_action('admin_post_hcdecor_media_ai_analyze',function(){
    if(!current_user_can('upload_files')) wp_die('Forbidden');
    check_admin_referer('hcdecor_media_ai_analyze');
    $ids=array_values(array_unique(array_filter(array_map('intval',(array)($_POST['media_ids']??[])))));
    $ok=0; $fail=0;
    foreach(array_slice($ids,0,12) as $id){
        $r=hcdecor_media_ai_analyze($id);
        is_wp_error($r)?$fail++:$ok++;
    }
    wp_safe_redirect(admin_url('admin.php?page=hcdecor-media&ai_ok='.$ok.'&ai_fail='.$fail)); exit;
});

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/media/(?P<id>\d+)/analyze',[
        'methods'=>'POST',
        'permission_callback'=>'hcdecor_ops_bridge_auth',
        'callback'=>function(WP_REST_Request $r){
            $res=hcdecor_media_ai_analyze((int)$r['id']);
            return is_wp_error($res)?$res:rest_ensure_response($res);
        }
    ]);
});
