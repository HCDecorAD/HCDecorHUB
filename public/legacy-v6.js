document.querySelectorAll('a[href^="#"]').forEach(a=>a.addEventListener('click',e=>{const t=document.querySelector(a.getAttribute('href'));if(t){e.preventDefault();t.scrollIntoView({behavior:'smooth'})}}));
const menuButton=document.querySelector('.menu-toggle'),menu=document.querySelector('.nav nav');
if(menuButton&&menu){menuButton.addEventListener('click',()=>{const open=menu.classList.toggle('open');menuButton.setAttribute('aria-expanded',String(open));});menu.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{menu.classList.remove('open');menuButton.setAttribute('aria-expanded','false');}));}
document.querySelectorAll('.project-filters button').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('.project-filters button').forEach(b=>b.classList.remove('active'));btn.classList.add('active');const f=btn.dataset.filter;document.querySelectorAll('.project').forEach(card=>card.classList.toggle('is-hidden',f!=='all'&&card.dataset.category!==f));}));

const i18n={
vi:{nav:['Trang chủ','Giới thiệu','Dịch vụ','Dự án','Quy trình','Liên hệ'],heroTitle:'Ý tưởng tạo nên<br><em>không gian khác biệt.</em>',lead:'Thiết kế · Thi công bảng hiệu · Nội thất · 3D & Phối cảnh · Media AI',project:'Xem dự án',service:'Khám phá dịch vụ',servicesTitle:'Giải pháp toàn diện<br>cho công trình',projectsTitle:'Công trình & ý tưởng nổi bật',processTitle:'Đơn giản · Rõ ràng · Hiệu quả'},
en:{nav:['Home','About','Services','Projects','Process','Contact'],heroTitle:'Ideas create<br><em>distinctive spaces.</em>',lead:'Design · Signage Construction · Interior · 3D Visualization · Media AI',project:'View projects',service:'Explore services',servicesTitle:'Complete solutions<br>for every project',projectsTitle:'Featured projects & ideas',processTitle:'Simple · Clear · Effective'}};
function setLang(lang){
 const d=i18n[lang]||i18n.vi; document.documentElement.lang=lang;
 document.querySelectorAll('.nav nav a').forEach((a,i)=>{if(d.nav[i])a.textContent=d.nav[i]});
 const h1=document.querySelector('.hero h1'); if(h1)h1.innerHTML=d.heroTitle;
 const lead=document.querySelector('.hero .lead'); if(lead)lead.textContent=d.lead;
 const acts=document.querySelectorAll('.hero .actions a'); if(acts[0])acts[0].textContent=d.project;if(acts[1])acts[1].textContent=d.service;
 const sh=document.querySelector('#services h2');if(sh)sh.innerHTML=d.servicesTitle;
 const ph=document.querySelector('#projects h2');if(ph)ph.textContent=d.projectsTitle;
 const qh=document.querySelector('#process h2');if(qh)qh.textContent=d.processTitle;
 const b=document.querySelector('.lang-toggle');if(b){b.querySelector('.lang-flag').textContent=lang==='vi'?'🇻🇳':'🇬🇧';b.querySelector('.lang-code').textContent=lang.toUpperCase();b.setAttribute('aria-label',lang==='vi'?'Switch to English':'Chuyển sang tiếng Việt');}
 localStorage.setItem('hcdecor-lang',lang);
}
const langButton=document.querySelector('.lang-toggle');
if(langButton)langButton.addEventListener('click',()=>setLang(document.documentElement.lang==='vi'?'en':'vi'));
setLang(localStorage.getItem('hcdecor-lang')||'vi');
