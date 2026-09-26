#!/usr/bin/env bash
set -Eeuo pipefail
trap 'echo; echo "HCDECOR BOOTSTRAP: FAIL at line $LINENO"; exit 1' ERR

pass(){ echo "[PASS] $1"; }
step(){ echo; echo "== $1 =="; }

step "Preflight"
wp core is-installed >/dev/null
pass "WordPress installed"

step "Theme + Elementor"
wp theme install hello-elementor --activate >/dev/null || wp theme activate hello-elementor >/dev/null
wp plugin is-installed elementor || wp plugin install elementor --activate
wp plugin activate elementor >/dev/null || true
wp theme is-active hello-elementor
wp plugin is-active elementor
pass "Hello Elementor + Elementor active"

step "HCDecor Core"
wp plugin is-installed hcdecor-core
wp plugin activate hcdecor-core >/dev/null || true
wp plugin is-active hcdecor-core
pass "HCDecor Core active"

step "Site configuration"
wp rewrite structure '/%postname%/' >/dev/null
wp rewrite flush >/dev/null
wp option update blogname 'HCDecor HUB' >/dev/null
wp option update blogdescription 'Thiết kế · Thi công · Nội thất · Kiến trúc · 3D' >/dev/null
wp option update elementor_disable_color_schemes yes >/dev/null
wp option update elementor_disable_typography_schemes yes >/dev/null
pass "Site options configured"

step "Homepage"
HOME_ID="$(wp eval '$p=get_page_by_path("trang-chu"); echo $p ? $p->ID : "";')"
test -n "$HOME_ID"
wp option update show_on_front page >/dev/null
wp option update page_on_front "$HOME_ID" >/dev/null
test "$(wp option get page_on_front)" = "$HOME_ID"
pass "Trang chủ assigned: ID $HOME_ID"

step "Required pages"
for slug in trang-chu gioi-thieu dich-vu-hcdecor du-an-hcdecor lien-he; do
  wp eval "$p=get_page_by_path('$slug'); if(!$p){fwrite(STDERR,'Missing $slug'.PHP_EOL); exit(1);}"
done
pass "5 required pages exist"

step "Services"
for name in "Bảng hiệu" "Nội thất" "3D & Phối cảnh" "Kiến trúc"; do
  wp eval "$p=get_page_by_title('$name',OBJECT,'hc_service'); if(!$p){fwrite(STDERR,'Missing service: $name'.PHP_EOL); exit(1);}"
done
pass "4 services exist"

step "Project taxonomy"
for name in "Bảng hiệu" "Nội thất" "3D & Phối cảnh" "Kiến trúc"; do
  wp eval "$t=term_exists('$name','hc_project_type'); if(!$t){fwrite(STDERR,'Missing project type: $name'.PHP_EOL); exit(1);}"
done
pass "4 project types exist"

step "REST API"
wp eval '$r=rest_do_request("/hcdecor/v1/site"); if($r->is_error() || $r->get_status()!==200){fwrite(STDERR,"REST failed".PHP_EOL);exit(1);} echo "REST 200".PHP_EOL;'
pass "HCDecor REST endpoint"

step "Elementor homepage readiness"
wp post meta update "$HOME_ID" _wp_page_template elementor_header_footer >/dev/null
wp post meta update "$HOME_ID" _elementor_edit_mode builder >/dev/null
pass "Homepage prepared for Elementor"

echo
echo "========================================"
echo "HCDECOR BOOTSTRAP: PASS"
echo "Homepage ID: $HOME_ID"
echo "Next: open WordPress > Pages > Trang chủ > Edit with Elementor"
echo "========================================"
