<?php

/**
 * Plugin Name: Devsroom Google Review ShowCase
 * Plugin URI:  https://devsroom.com
 * Description: Fetch and display verified Google My Business reviews via shortcode and Elementor widget with multiple layouts.
 * Version:     0.0.1
 * Author:      Devsroom
 * Author URI:  https://devsroom.com
 * License:     GPL-2.0-or-later
 * Text Domain: devsroom-google-review-showcase
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.7
 * Requires PHP: 8.1
 */

if (! defined('ABSPATH')) {
    exit;
}

define('DEVSROOM_GREVIEWS_VERSION', '1.0.0');
define('DEVSROOM_GREVIEWS_DIR', plugin_dir_path(__FILE__));
define('DEVSROOM_GREVIEWS_URL', plugin_dir_url(__FILE__));
define('DEVSROOM_GREVIEWS_BASENAME', plugin_basename(__FILE__));

/**
 * Initialize the plugin.
 */
function devsroom_greviews_init()
{
    // Core classes
    require_once DEVSROOM_GREVIEWS_DIR . 'includes/class-cache.php';
    require_once DEVSROOM_GREVIEWS_DIR . 'includes/class-api-fetch.php';
    require_once DEVSROOM_GREVIEWS_DIR . 'includes/class-ordering.php';
    require_once DEVSROOM_GREVIEWS_DIR . 'includes/class-render.php';
    require_once DEVSROOM_GREVIEWS_DIR . 'includes/class-shortcode.php';

    // Admin settings (admin only)
    if (is_admin()) {
        require_once DEVSROOM_GREVIEWS_DIR . 'includes/class-admin-settings.php';
        new Devsroom_GReviews_Admin_Settings();
    }

    // Shortcode (always loaded)
    new Devsroom_GReviews_Shortcode();

    // Elementor integration (optional)
    if (did_action('elementor/loaded')) {
        require_once DEVSROOM_GREVIEWS_DIR . 'includes/elementor/class-elementor-init.php';
        new Devsroom_GReviews_Elementor_Init();
    }
}
add_action('plugins_loaded', 'devsroom_greviews_init');

/**
 * Register assets (not enqueued — render engine enqueues on demand).
 */
function devsroom_greviews_register_assets()
{
    // Main CSS
    wp_register_style(
        'devsroom-greviews',
        DEVSROOM_GREVIEWS_URL . 'assets/css/style.css',
        array(),
        DEVSROOM_GREVIEWS_VERSION
    );

    // Swiper (CDN)
    wp_register_style(
        'swiper-css',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        array(),
        '11'
    );
    wp_register_script(
        'swiper',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        array(),
        '11',
        true
    );

    // Plugin JS
    wp_register_script(
        'devsroom-greviews-frontend',
        DEVSROOM_GREVIEWS_URL . 'assets/js/frontend.js',
        array(),
        DEVSROOM_GREVIEWS_VERSION,
        true
    );
    wp_register_script(
        'devsroom-greviews-slider',
        DEVSROOM_GREVIEWS_URL . 'assets/js/slider.js',
        array('swiper'),
        DEVSROOM_GREVIEWS_VERSION,
        true
    );
    wp_register_script(
        'devsroom-greviews-masonry',
        DEVSROOM_GREVIEWS_URL . 'assets/js/masonry.js',
        array(),
        DEVSROOM_GREVIEWS_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'devsroom_greviews_register_assets');

/**
 * Activation hook.
 */
function devsroom_greviews_activate()
{
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'devsroom_greviews_activate');

/**
 * Deactivation hook.
 */
function devsroom_greviews_deactivate()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'devsroom_greviews_deactivate');
