<?php
if (!defined('ABSPATH')) exit;

/* HCDecor Media Intelligence: AI analysis, metadata suggestions, cover scoring. */

function hcdecor_media_rest_approval_fresh(WP_REST_Request $r){
    if(!rest_sanitize_boolean($r->get_param('production_approved'))) return false;
    $by_raw=$r->get_param('production_approved_by');$source_raw=$r->get_param('production_approval_source');$at_raw=$r->get_param('production_approved_at');
    $by=is_scalar($by_raw)?sanitize_text_field((string)$by_raw):'';
    $source=is_scalar($source_raw)?sanitize_key((string)$source_raw):'';
    $at_raw=is_scalar($at_raw)?(string)$at_raw:'';
    if($by==='' || strlen($by)>200 || strlen($source)>50 || strlen($at_raw)>64 || !in_array($source,['wp_user','service_bridge'],true)) return false;
    $at=strtotime($at_raw)?:0;
    return $at>=time()-15*MINUTE_IN_SECONDS && $at<=time()+5*MINUTE_IN_SECONDS;
}

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
    if($raw===false || strlen($raw)!==$size) return new WP_Error('read','Cannot read image safely.');
    $mime=(string)get_post_mime_type($attachment_id);
    if(!in_array($mime,['image/jpeg','image/png','image/webp','image/gif'],true)) return new WP_Error('mime','Unsupported image type.');
    return 'data:'.$mime.';base64,'.base64_encode($raw);
}

function hcdecor_media_ai_prompt($attachment_id){
    $clip=function($value,$limit){$value=(string)$value;return function_exists('mb_substr')?mb_substr($value,0,$limit):substr($value,0,$limit);};
    $title=$clip(get_the_title($attachment_id),500);
    $project_ids=get_posts([
        'post_type'=>'hc_project','post_status'=>['publish','draft'],'numberposts'=>20,'fields'=>'ids',
        'meta_query'=>[['key'=>'hc_project_gallery','value'=>'"'.(int)$attachment_id.'"','compare'=>'LIKE']]
    ]);
    $project_titles=array_map(function($id)use($clip){return $clip(get_the_title($id),500);},$project_ids);
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
    if(!function_exists('hcdecor_ai_model') || hcdecor_ai_model('openai')==='') return new WP_Error('model','OpenAI model chưa cấu hình.');
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
    $r=wp_safe_remote_post('https://api.openai.com/v1/responses',[
        'timeout'=>90,'redirection'=>0,'limit_response_size'=>2*1024*1024,'headers'=>['Authorization'=>'Bearer '.$key,'Content-Type'=>'application/json'],
        'body'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
    ]);
    if(is_wp_error($r)) return $r;
    $status=(int)wp_remote_retrieve_response_code($r); $body=(string)wp_remote_retrieve_body($r);
    if(strlen($body)>2*1024*1024) return new WP_Error('response_size','AI media response exceeds 2 MB.');
    $data=json_decode($body,true);
    if($status<200||$status>=300) return new WP_Error('openai',function_exists('hcdecor_ai_safe_error')?hcdecor_ai_safe_error((string)($data['error']['message']??('OpenAI HTTP '.$status))):('OpenAI HTTP '.$status));
    $text=function_exists('hcdecor_ai_extract_openai_text')?hcdecor_ai_extract_openai_text((array)$data):'';
    $json=json_decode($text,true);
    return is_array($json)?['provider'=>'openai','data'=>$json]:new WP_Error('json','OpenAI media JSON invalid.');
}

function hcdecor_media_ai_call_gemini($attachment_id){
    $key=function_exists('hcdecor_ai_secret')?hcdecor_ai_secret('gemini'):'';
    if(!$key) return new WP_Error('key','Gemini chưa cấu hình.');
    if(!function_exists('hcdecor_ai_model') || hcdecor_ai_model('gemini')==='') return new WP_Error('model','Gemini model chưa cấu hình.');
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
    $r=wp_safe_remote_post('https://generativelanguage.googleapis.com/v1beta/interactions',[
        'timeout'=>90,
        'redirection'=>0,
        'limit_response_size'=>2*1024*1024,
        'headers'=>['x-goog-api-key'=>$key,'Content-Type'=>'application/json','Api-Revision'=>'2026-05-20'],
        'body'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
    ]);
    if(is_wp_error($r)) return $r;
    $status=(int)wp_remote_retrieve_response_code($r); $body=(string)wp_remote_retrieve_body($r);
    if(strlen($body)>2*1024*1024) return new WP_Error('response_size','AI media response exceeds 2 MB.');
    $data=json_decode($body,true);
    if($status<200||$status>=300) return new WP_Error('gemini',function_exists('hcdecor_ai_safe_error')?hcdecor_ai_safe_error((string)($data['error']['message']??('Gemini HTTP '.$status))):('Gemini HTTP '.$status));
    $text=function_exists('hcdecor_ai_extract_gemini_text')?hcdecor_ai_extract_gemini_text((array)$data):'';
    $json=json_decode($text,true);
    return is_array($json)?['provider'=>'gemini','data'=>$json]:new WP_Error('json','Gemini media JSON invalid.');
}

function hcdecor_media_ai_analyze($attachment_id){
    $attachment_id=(int)$attachment_id;
    if(!function_exists('hcdecor_ai_worker_enabled') || !hcdecor_ai_worker_enabled()) return new WP_Error('ai_disabled','AI worker master switch is disabled.');
    if(get_post_type($attachment_id)!=='attachment') return new WP_Error('media','Invalid media.');
    $errors=[];
    $order=function_exists('hcdecor_ai_provider_order')?hcdecor_ai_provider_order():['openai','gemini'];
    foreach($order as $provider){
        $r=$provider==='openai'?hcdecor_media_ai_call_openai($attachment_id):hcdecor_media_ai_call_gemini($attachment_id);
        if(is_wp_error($r)){ $errors[]=$provider.': '.(function_exists('hcdecor_ai_safe_error')?hcdecor_ai_safe_error($r->get_error_message()):sanitize_text_field($r->get_error_message())); continue; }
        $d=$r['data'];
        $clip=function($value,$limit,$textarea=false){
            $text=$textarea?sanitize_textarea_field((string)$value):sanitize_text_field((string)$value);
            return function_exists('mb_substr')?mb_substr($text,0,$limit):substr($text,0,$limit);
        };
        update_post_meta($attachment_id,'hc_ai_summary',$clip($d['summary']??'',5000,true));
        update_post_meta($attachment_id,'hc_ai_alt',$clip($d['alt']??'',500));
        update_post_meta($attachment_id,'hc_ai_caption',$clip($d['caption']??'',5000,true));
        $tags=[];
        foreach(array_slice((array)($d['tags']??[]),0,30) as $tag){ if(is_scalar($tag)){ $tag=$clip($tag,100); if($tag!=='') $tags[]=$tag; } }
        update_post_meta($attachment_id,'hc_ai_tags',array_values(array_unique($tags)));
        update_post_meta($attachment_id,'hc_ai_visual_type',$clip($d['visual_type']??'',500));
        update_post_meta($attachment_id,'hc_ai_cover_score',max(0,min(100,(int)($d['cover_score']??0))));
        update_post_meta($attachment_id,'hc_ai_provider',sanitize_key($r['provider']));
        update_post_meta($attachment_id,'hc_ai_analyzed_at',current_time('mysql'));
        delete_post_meta($attachment_id,'hc_ai_error');
        return $r;
    }
    $msg=implode(' | ',$errors);
    $msg=function_exists('hcdecor_ai_safe_error')?hcdecor_ai_safe_error($msg):sanitize_text_field($msg);
    update_post_meta($attachment_id,'hc_ai_error',$msg);
    return new WP_Error('ai_failed',$msg?:'No AI provider available.');
}

function hcdecor_media_ai_apply_project_recommendations($project_id){
    $project_id=(int)$project_id;
    if(!$project_id || get_post_type($project_id)!=='hc_project') return new WP_Error('project','Invalid project.');
    $media_raw=get_post_meta($project_id,'hc_project_gallery',true);$media=is_array($media_raw)?$media_raw:[];
    if(!$media){$legacy_raw=get_post_meta($project_id,'hc_gallery_ids',true);$media=is_array($legacy_raw)?$legacy_raw:[];}
    $media=array_slice(array_values(array_filter(array_unique(array_map('intval',$media)),function($id){return get_post_type($id)==='attachment';})),0,60);
    if(!$media) return new WP_Error('media','Project has no media.');

    $ranked=[];
    foreach($media as $mid){
        if(get_post_type($mid)!=='attachment') continue;
        $analyzed_raw=get_post_meta($mid,'hc_ai_analyzed_at',true);$analyzed=is_scalar($analyzed_raw)?(string)$analyzed_raw:'';$analyzed=function_exists('mb_substr')?mb_substr($analyzed,0,64):substr($analyzed,0,64);
        $score=$analyzed!==''?(int)get_post_meta($mid,'hc_ai_cover_score',true):-1;
        $ranked[]=['id'=>$mid,'score'=>$score,'analyzed'=>$analyzed!==''?1:0];
        $alt_raw=get_post_meta($mid,'hc_ai_alt',true);$alt=is_scalar($alt_raw)?(string)$alt_raw:'';
        $caption_raw=get_post_meta($mid,'hc_ai_caption',true);$caption=is_scalar($caption_raw)?(string)$caption_raw:'';
        $alt=function_exists('mb_substr')?mb_substr($alt,0,1000):substr($alt,0,1000);
        $caption=function_exists('mb_substr')?mb_substr($caption,0,5000):substr($caption,0,5000);
        if($alt!=='' && get_post_meta($mid,'_wp_attachment_image_alt',true)==='') update_post_meta($mid,'_wp_attachment_image_alt',$alt);
        $post=get_post($mid);
        if($post && $caption!=='' && trim((string)$post->post_excerpt)==='') wp_update_post(['ID'=>$mid,'post_excerpt'=>$caption]);
    }
    if(!$ranked) return new WP_Error('media','No valid project media.');
    usort($ranked,function($a,$b){ if($a['analyzed']!==$b['analyzed']) return $b['analyzed']<=>$a['analyzed']; return $b['score']<=>$a['score']; });
    $cover=(int)$ranked[0]['id'];
    update_post_meta($project_id,'hc_ai_recommended_cover_id',$cover);
    update_post_meta($project_id,'hc_ai_media_rank',array_column($ranked,'id'));
    update_post_meta($project_id,'hc_ai_media_recommended_at',current_time('mysql'));
    if(!has_post_thumbnail($project_id) && wp_attachment_is_image($cover)) set_post_thumbnail($project_id,$cover);
    do_action('hcdecor_project_data_changed',$project_id);
    return ['cover_id'=>$cover,'media_ids'=>array_column($ranked,'id')];
}

add_action('admin_post_hcdecor_media_ai_analyze',function(){
    if(!current_user_can('upload_files')) wp_die('Forbidden');
    check_admin_referer('hcdecor_media_ai_analyze');
    $ids=array_values(array_filter(array_unique(array_map('intval',(array)($_POST['media_ids']??[]))),function($id){ return get_post_type($id)==='attachment' && current_user_can('edit_post',$id); }));
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
            $id=(int)$r['id'];
            if(!$id || get_post_type($id)!=='attachment') return new WP_Error('media','Invalid media.',['status'=>404]);
            if(get_current_user_id()>0 && !current_user_can('edit_post',$id)) return new WP_Error('forbidden','Forbidden.',['status'=>403]);
            if(!hcdecor_media_rest_approval_fresh($r)) return new WP_Error('approval','Fresh explicit approval is required for AI media analysis.',['status'=>403]);
            $res=hcdecor_media_ai_analyze($id);
            return is_wp_error($res)?$res:rest_ensure_response($res);
        }
    ]);
});
