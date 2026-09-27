<?php
/**
 * Plugin Name: HCDecor HUB Host Router
 * Description: Host-aware routing for HCDecor HUB brands.
 * Version: 0.1.0
 */
if (!defined('ABSPATH')) exit;

final class HCDecor_Host_Router {
  private const MAP = [
    'gscsenior.hcdecorhub.com' => '/gsc-luxury-home-v2/',
    'amonnguyen.hcdecorhub.com' => '/amo-storefront-v2/',
  ];

  public static function boot(): void {
    add_action('template_redirect', [__CLASS__, 'route'], 0);
  }

  public static function route(): void {
    if (is_admin() || wp_doing_ajax() || defined('REST_REQUEST') && REST_REQUEST) return;
    $host = strtolower(preg_replace('/:\\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
    if (!isset(self::MAP[$host])) return;

    $request = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($request !== '/' && $request !== '') return;

    $target = home_url(self::MAP[$host]);
    wp_safe_redirect($target, 302, 'HCDecor HUB Host Router');
    exit;
  }
}
HCDecor_Host_Router::boot();
