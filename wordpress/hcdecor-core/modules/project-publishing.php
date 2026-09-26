<?php
if (!defined('ABSPATH')) exit;

/* Production project presentation for HCDecor content published from Content Operations. */

add_shortcode('hcdecor_project_grid', function($atts){
    $a=shortcode_atts(['limit'=>12,'type'=>''],$atts);
    $args=['post_type'=>'hc_project','post_status'=>'publish','posts_per_page'=>max(1,min(48,(int)$a['limit'])),'orderby'=>'modified','order'=>'DESC'];
    if($a['type']) $args['tax_query']=[['taxonomy'=>'hc_project_type','field'=>'slug','terms'=>sanitize_title($a['type'])]];
    $q=new WP_Query($args);
    ob_start(); ?>
    <div class="hc-project-grid">
    <?php while($q->have_posts()):$q->the_post(); $img=get_the_post_thumbnail_url(get_the_ID(),'large');?>
      <article class="hc-project-card"><a href="<?php the_permalink();?>">
        <div class="hc-project-thumb"<?php echo $img?' style="background-image:url('.esc_url($img).')"':'';?>></div>
        <div class="hc-project-copy"><h3><?php the_title();?></h3><?php if(has_excerpt()):?><p><?php echo esc_html(get_the_excerpt());?></p><?php endif;?></div>
      </a></article>
    <?php endwhile; wp_reset_postdata();?>
    </div><?php return ob_get_clean();
});

add_action('wp_enqueue_scripts',function(){
    wp_register_style('hcdecor-projects',false,[], '1.0.0'); wp_enqueue_style('hcdecor-projects');
    wp_add_inline_style('hcdecor-projects','
    .hc-project-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin:24px 0}
    .hc-project-card{border:1px solid #272d34;border-radius:14px;overflow:hidden;background:#10151b}.hc-project-card a{text-decoration:none;color:inherit}
    .hc-project-thumb{aspect-ratio:4/3;background:#20262d center/cover no-repeat}.hc-project-copy{padding:16px}.hc-project-copy h3{margin:0 0 7px;font-size:20px}.hc-project-copy p{margin:0;color:#8f98a3}
    .hc-project-gallery{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:26px 0}.hc-project-gallery figure{margin:0}.hc-project-gallery img{display:block;width:100%;height:auto;border-radius:12px}.hc-project-gallery figcaption{font-size:12px;color:#777;margin-top:5px}
    @media(max-width:900px){.hc-project-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.hc-project-grid,.hc-project-gallery{grid-template-columns:1fr}}
    ');
});

add_filter('the_content',function($content){
    if(!is_singular('hc_project') || !in_the_loop() || !is_main_query()) return $content;
    $ids=(array)get_post_meta(get_the_ID(),'hc_project_gallery',true); $ids=array_values(array_filter(array_map('intval',$ids)));
    if(!$ids) return $content;
    $html='<div class="hc-project-gallery" aria-label="Project gallery">';
    foreach($ids as $id){$img=wp_get_attachment_image($id,'large',false,['loading'=>'lazy']);if(!$img)continue;$cap=wp_get_attachment_caption($id);$html.='<figure>'.$img.($cap?'<figcaption>'.esc_html($cap).'</figcaption>':'').'</figure>';}
    return $content.$html.'</div>';
},20);

add_action('rest_api_init',function(){
    register_rest_route('hcdecor/v1','/projects-public',['methods'=>'GET','permission_callback'=>'__return_true','callback'=>function(){
        $posts=get_posts(['post_type'=>'hc_project','post_status'=>'publish','numberposts'=>50,'orderby'=>'modified','order'=>'DESC']);
        return rest_ensure_response(array_map(function($p){
            $media=(array)get_post_meta($p->ID,'hc_project_gallery',true);
            return ['id'=>$p->ID,'title'=>$p->post_title,'excerpt'=>$p->post_excerpt,'url'=>get_permalink($p),'featured'=>get_the_post_thumbnail_url($p->ID,'large')?:null,'media'=>array_values(array_filter(array_map(function($id){return wp_get_attachment_url((int)$id);},$media)))];
        },$posts));
    }]);
});

add_action('init',function(){
    if(get_option('hcdecor_project_page_v1')) return;
    $page=get_page_by_path('du-an-hcdecor');
    if($page && trim((string)$page->post_content)==='') wp_update_post(['ID'=>$page->ID,'post_content'=>'[hcdecor_project_grid limit="24"]']);
    update_option('hcdecor_project_page_v1',1,false);
},30);
