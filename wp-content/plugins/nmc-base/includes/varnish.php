<?php

/**
 * NMC Varnish Functionality
 *
 * Issue a BAN for various actions
 * within Wordpress.
 */

add_action('admin_init', 'nmc_varnish_init');

function nmc_varnish_init()
{
    $actions = [
        'autoptimize_action_cachepurged',
        'delete_attachment',
        'deleted_post',
        'edit_post',
        'import_start',
        'import_end',
        'save_post',
        'switch_theme',
        'trashed_post'
    ];

    $actions = apply_filters('nmc_varnish_ban_actions', $actions);

    foreach ($actions as $actionKey) {
        add_action($actionKey, 'nmc_varnish_ban_site');
    }
}

/**
 * Varnish Ban Site
 *
 * Run a varnish ban for the entire domain.
 *
 * @return void
 */
function nmc_varnish_ban_site()
{
    $curl = curl_init('http://127.0.0.1');

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Host: '.wp_parse_url(get_site_url(), PHP_URL_HOST)]);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'BAN');

    $resp = curl_exec($curl);
}

/**
 * Cache Clear Link in Admin Bar
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

/**
 * Cache Clear Action
 */
add_action('wp_ajax_nmc_clear_site_varnish', 'nmc_clear_site_varnish');
function nmc_clear_site_varnish()
{
    $caps = apply_filters('nmc_cache_clear_capabilities', ['publish_posts']);

    if (nmc_current_user_can_any($caps)) {
        nmc_varnish_ban_site();

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
