#!/usr/bin/env bash
set -euo pipefail

echo "== HCDecor WordPress bootstrap =="
wp core is-installed >/dev/null || { echo "ERROR: Run this inside Local > Site shell."; exit 1; }

echo "[1/7] Theme"
wp theme install hello-elementor --activate

echo "[2/7] Elementor"
wp plugin install elementor --activate

echo "[3/7] HCDecor Core"
if wp plugin is-installed hcdecor-core; then
  wp plugin activate hcdecor-core
else
  echo "HCDecor Core is not installed in wp-content/plugins/hcdecor-core."
  echo "Copy/install the plugin first, then rerun this command."
  exit 2
fi

echo "[4/7] Permalinks"
wp rewrite structure '/%postname%/' --hard
wp rewrite flush --hard

echo "[5/7] Homepage"
HOME_ID=$(wp post list --post_type=page --name=trang-chu --field=ID --format=ids | awk '{print $1}')
if [ -n "${HOME_ID:-}" ]; then
  wp option update show_on_front page
  wp option update page_on_front "$HOME_ID"
fi

echo "[6/7] Elementor settings"
wp option update elementor_disable_color_schemes yes
wp option update elementor_disable_typography_schemes yes
wp option update blogname 'HCDecor HUB'
wp option update blogdescription 'Thiết kế · Thi công · Nội thất · Kiến trúc · 3D'

echo "[7/7] Validation"
wp core version
wp theme status hello-elementor
wp plugin status elementor
wp plugin status hcdecor-core
wp post list --post_type=page --fields=ID,post_title,post_status --format=table
wp post list --post_type=hc_service --fields=ID,post_title,post_status --format=table
echo "REST:"
wp eval 'echo rest_url("hcdecor/v1/site").PHP_EOL;'
echo "== HCDecor bootstrap PASS =="
