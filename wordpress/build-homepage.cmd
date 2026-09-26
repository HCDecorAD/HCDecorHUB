@echo off
setlocal EnableExtensions
echo == HCDecor Homepage Builder ==

call wp core is-installed || goto :fail
for /f "delims=" %%I in ('wp eval "$p=get_page_by_path('trang-chu'); echo $p ? $p->ID : '';"') do set HOME_ID=%%I
if not defined HOME_ID goto :fail

echo [1/4] Building Elementor homepage data...
call wp eval-file wp-content/plugins/hcdecor-core/homepage-builder.php || goto :fail

echo [2/4] Elementor CSS regeneration...
call wp elementor flush-css >nul 2>&1
if errorlevel 1 echo [WARN] Elementor CLI flush unavailable; editor will regenerate CSS.

echo [3/4] Homepage validation...
call wp eval "$id=(int)get_option('page_on_front'); $d=get_post_meta($id,'_elementor_data',true); if(!$id || !$d || strlen($d)<500){fwrite(STDERR,'Homepage Elementor data missing'.PHP_EOL);exit(1);} echo 'Elementor data bytes: '.strlen($d).PHP_EOL;" || goto :fail

echo [4/4] Final...
echo.
echo ========================================
echo HCDECOR HOMEPAGE: PASS
echo Homepage ID: %HOME_ID%
echo Open: http://hcdecor-hub.local/
echo ========================================
exit /b 0
:fail
echo.
echo HCDECOR HOMEPAGE: FAIL
exit /b 1
