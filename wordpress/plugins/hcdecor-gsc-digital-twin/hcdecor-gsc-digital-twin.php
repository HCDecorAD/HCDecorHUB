<?php
/**
 * Plugin Name: HCDecor GSC Digital Twin
 * Description: Interactive GSC hotspot layer using verified GSC.Web coordinates and video sources.
 * Version: 0.2.0
 */
if (!defined('ABSPATH')) exit;

add_shortcode('gsc_digital_twin', function () {
  $hotspots = [
    [1,'Mặt tiền – Cổng vào',50,88,'1i9utBhQ9oQotJOxm6ORpWGwiPCCfDIz7'],
    [2,'Khu đánh cờ – Thư giãn',31,67,'10ucS4VQeHpWCgrefvWx1TGZYcUg0aHaC'],
    [3,'Khu tập ngoài trời',70,67,'1lqFuUTKKf9A0w4g04F2aB2OpRJdT42N8'],
    [4,'Hồ cá Koi',50,64,'1cZVjsaQUPYN89IEI4u0L-wt9-ndaKzmN'],
    [5,'Cổng chính tòa nhà',50,51,'1ZAhZ67i4lBJsu3fVxPQNYd8AKJ9L3sKF'],
    [6,'Hoạt động ngoài trời tầng 3',70.5,45.5,'1g0mAVPZzEMeLk1QSDyX6gLhwMIqg-Qfm'],
    [7,'Hai tháp đôi',50,36,'1BMgtO93Kwr90AdS9uH0ueGAv5DHdX0E4'],
    [8,'Hồ bơi',68,16,'1OgiVVHld9esVkVO2QgwCNdVMKmhVzcSF'],
    [9,'Trồng trọt – Tâm linh',72,7.5,'1urOJaD3rrcx5rmhlJh10EsHMTQoaIk5_'],
    [10,'Bãi xe',40,10.5,'1-bqRC1kpnICxd8Yn6NquCGCt4dybqzYu'],
    [11,'Sân thượng',61.5,31,'']
  ];
  ob_start(); ?>
  <section class="gscdt" aria-label="GSC Digital Twin">
    <div class="gscdt-stage">
      <img src="https://hcdecorhub.com/wp-content/uploads/2026/09/GSC-SENIOR-LIVING-AND-WELLNESS-Cover.jpg" alt="GSC Senior Living & Wellness">
      <?php foreach ($hotspots as $h): ?>
        <button class="gscdt-pin" style="left:<?=esc_attr($h[2])?>%;top:<?=esc_attr($h[3])?>%" data-name="<?=esc_attr($h[1])?>" data-video="<?=esc_attr($h[4])?>" aria-label="<?=esc_attr($h[1])?>"><span><?=intval($h[0])?></span></button>
      <?php endforeach; ?>
    </div>
    <div class="gscdt-modal" hidden><button class="gscdt-close" aria-label="Đóng">×</button><h3></h3><div class="gscdt-media"></div></div>
  </section>
  <style>
  .gscdt{position:relative;background:#06100c;color:#f3ead7}.gscdt-stage{position:relative;overflow:hidden}.gscdt-stage>img{width:100%;display:block}.gscdt-pin{position:absolute;transform:translate(-50%,-50%);width:42px;height:42px;border-radius:50%;border:1px solid #f0d28e;background:rgba(7,16,12,.78);color:#f0d28e;cursor:pointer;box-shadow:0 0 0 7px rgba(240,210,142,.13);animation:gscpulse 2s infinite}.gscdt-pin:hover{scale:1.12}.gscdt-modal{position:fixed;z-index:99999;inset:5vh 5vw;background:#07100d;padding:24px;border:1px solid #8d7545;box-shadow:0 30px 80px #000}.gscdt-modal iframe{width:100%;height:70vh;border:0}.gscdt-close{float:right;font-size:34px;background:none;border:0;color:#fff;cursor:pointer}@keyframes gscpulse{50%{box-shadow:0 0 0 14px rgba(240,210,142,0)}}@media(max-width:700px){.gscdt-pin{width:32px;height:32px}.gscdt-modal{inset:2vh 2vw}.gscdt-modal iframe{height:55vh}}
  </style>
  <script>
  document.addEventListener('click',function(e){const p=e.target.closest('.gscdt-pin'),m=document.querySelector('.gscdt-modal');if(p&&m){m.querySelector('h3').textContent=p.dataset.name;const box=m.querySelector('.gscdt-media');box.innerHTML=p.dataset.video?'<iframe allow="autoplay; fullscreen" allowfullscreen src="https://drive.google.com/file/d/'+p.dataset.video+'/preview"></iframe>':'<p>Video cho điểm trải nghiệm này chưa được gán.</p>';m.hidden=false}if(e.target.closest('.gscdt-close')&&m){m.hidden=true;m.querySelector('.gscdt-media').innerHTML=''}})
  </script>
  <?php return ob_get_clean();
});
