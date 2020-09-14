<?php

/**
 * Core Customizations
 *
 * Set of customizations to the Wordpress
 * core that are used on every site.
 */

/**
 * Theme Support
 */
add_action('after_setup_theme', function () {
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list']);
    add_theme_support('menus');
});

/**
 * Core Wordpress feature disabling
 */
add_action('after_setup_theme', function () {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('set_comment_cookies', 'wp_set_comment_cookies');
    add_filter('the_generator', '__return_false');
});

/**
 * Core Wordpress admin bar feature disabling
 */
add_action('admin_bar_menu', function ($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
    $wp_admin_bar->remove_node('comments');
    $wp_admin_bar->remove_node('new-content');
    $wp_admin_bar->remove_node('customize');
}, 999);

/**
 * Remove slash from the end of all URLs
 *
 * If this is theme version 4.2 or higher, we are forcing
 * urls to omit the closing slash.
 */
add_filter('user_trailingslashit', function ($url) {
    if (nmc_theme_version() >= 5.0 or apply_filters('nmc_ignore_last_slash_in_url_mode', false)) {
        return rtrim($url, '/');
    }
    return $url;
});

/**
 * Stop redirections from slash to non-slash
 *
 * We want both - / and non-/ to resolve, so we will
 * check to see if that's the only difference between these two URLs
 * and then cancel the redirect if it is.
 */
add_filter('redirect_canonical', function ($redirect_url, $requested_url) {
    if (nmc_theme_version() >= 5.0 or apply_filters('nmc_ignore_last_slash_in_url_mode', false)) {
        if ($redirect_url.'/' == $requested_url) {
            return $requested_url;
        }
    }
    return $redirect_url;
}, 1, 2);

/**
 * Gutenberg Admin UI Customizations
 *
 * https://developer.wordpress.org/block-editor/developers/themes/theme-support/
 */
function nmc_gutenberg_style_customizations()
{
    if (nmc_theme_version() >= 5.0) {

        // Add support for editor styles.
        add_theme_support('editor-styles');
      
        // Enqueue editor styles.
        add_editor_style('css/blocks/all.min.css');
    }

    // Disable manual font sizes
    add_theme_support('disable-custom-font-sizes');

    // Disable custom colors in the block palette
    add_theme_support('disable-custom-colors');

    // Remove color palette by setting an empty palette
    add_theme_support('editor-color-palette', []);
}
add_action('after_setup_theme', 'nmc_gutenberg_style_customizations');

/**
 * Admin Menu Removals
 */
add_action('admin_menu', function () {
    global $submenu;

    // Remove Tools -> Customize
    unset($submenu['themes.php'][6]);
});

/**
 * Wordpress Admin Dashboard Customizations
 *
 * Remove some default, often unused
 * dashboard widgets
 */
add_action('wp_dashboard_setup', function () {
    global $wp_meta_boxes;

    $whitelist = apply_filters('nmc_dashboard_metabox_whitelist', []);

    $normal = [
        'dashboard_incoming_links',
        'dashboard_right_now',
        'dashboard_plugins',
        'dashboard_recent_drafts',
        'dashboard_recent_comments',
        'dashboard_site_health'
    ];

    foreach ($normal as $slug) {
        if (!in_array($slug, $whitelist) and isset($wp_meta_boxes['dashboard']['normal']['core'][$slug])) {
            unset($wp_meta_boxes['dashboard']['normal']['core'][$slug]);
        }
    }

    $side = [
        'dashboard_quick_press',
        'dashboard_primary',
        'dashboard_secondary'
    ];

    foreach ($side as $slug) {
        if (!in_array($slug, $whitelist) and isset($wp_meta_boxes['dashboard']['side']['core'][$slug])) {
            unset($wp_meta_boxes['dashboard']['side']['core'][$slug]);
        }
    }
});

/**
 * Remove Formdiable Add-On Functionality
 *
 * Formdiable comes with its own mini add-on store
 * that does not respect the DISALLOW_FILE_MODS
 * setting. This manually removes the Formidable
 * addon installation capabiltiies.
 */
add_action('admin_menu', function () {
    remove_submenu_page('formidable', 'formidable-addons');
}, 200);

// We want to make sure we are disable the add-on activation
// action, but not for when we are activating formdiable-pro.
$requestIsFormidableLicenseActivation = false;
if (
    (isset($_POST['action']) and isset($_POST['plugin']))
    and $_POST['action'] == 'frm_addon_activate'
    and $_POST['plugin'] == 'formidable_pro'
) {
    $requestIsFormidableLicenseActivation = true;
}

if ($requestIsFormidableLicenseActivation === false) {
    remove_all_actions('wp_ajax_frm_addon_activate');
}
remove_all_actions('wp_ajax_frm_addon_deactivate');
remove_all_actions('wp_ajax_frm_install_addon');
remove_all_actions('wp_ajax_frm_activate_addon');

/**
 * Cache Clear Link in Admin Bar
 *
 * Adds a link in the admin bar to clear
 * the entire site cache.
 */
add_action('admin_bar_menu', 'nmc_add_varnish_cache_clear_button', 100);
function nmc_add_varnish_cache_clear_button($admin_bar)
{
    $caps = apply_filters('nmc_cache_clear_capabilities', ['publish_posts']);

    if (nmc_current_user_can_any($caps)) {
        $admin_bar->add_menu([
            'id'    => 'nmc-clear-cache',
            'title' => '<span class="ab-icon dashicons dashicons-update"></span>' . 'Clear Site Cache',
            'href'  => '/wp-admin/admin-ajax.php?action=nmc_clear_site_varnish',
            'meta'  => array(
                'title' => __('Clear Site Cache'),
            ),
        ]);
    }
}

add_action('wp_ajax_nmc_clear_site_varnish', 'nmc_clear_site_varnish');
function nmc_clear_site_varnish()
{
    $caps = apply_filters('nmc_cache_clear_capabilities', ['publish_posts']);

    if (nmc_current_user_can_any($caps)) {
        \NMC_WP\Cache\Purger::banSite();

        // Wordpress doesn't use PHP sessions, so here we are.
        set_transient('cache_clear_notice', 1);

        wp_redirect($_SERVER['HTTP_REFERER']);
        exit;
    }
}

function nmc_cache_clear_notice()
{
    if (get_transient('cache_clear_notice')) {
        ?>
    <div class="updated notice">
        <p><?php _e('The site cache has been cleared.', 'nmc_cache_clear'); ?></p>
    </div>
    <?php
    delete_transient('cache_clear_notice');
    }
}
add_action('admin_notices', 'nmc_cache_clear_notice');
