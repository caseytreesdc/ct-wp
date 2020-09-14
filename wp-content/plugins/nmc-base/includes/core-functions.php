<?php

/**
 * NMC Core Functions
 *
 * Functions that are included in every Wordpress
 * installation. Only functions that can exists as non-customizable,
 * site-wide functionality should be put here.
 */

/**
 * NMC Blocks Name
 *
 * There are certain functions in the NMC Blocks plugin that reference
 * the blocks ACF field. If the blocks name is changed in the WP Base theme,
 * it can be changed in the plugin with the nmc_blocks_name filter.
 */
if (!function_exists('nmc_blocks_name')) {
    function nmc_blocks_name()
    {
        return apply_filters('nmc_blocks_name', 'nmc_blocks');
    }
}

/**
 * Sanitize Array
 *
 * Helper function to access the wordpressSanitizeArray() function
 * of the \NMC_WP\Sanitize class.
 */
if (!function_exists('wp_sanitize_array')) {
    function wp_sanitize_array($array, $method)
    {
        $sanitize = new \NMC_WP\Sanitize;
        return $sanitize->wordpressSanitizeArray($array, $method);
    }
}

/**
 * Sanitize Slug Array
 *
 * Helper function to access the wordpressSanitizeArray() function
 * of the \NMC_WP\Sanitize class. Used
 */
if (!function_exists('wp_sanitize_slug_array')) {
    function wp_sanitize_slug_array($array)
    {
        return wp_sanitize_array($array, 'sanitize_title');
    }
}

/**
 * Get Theme Version
 *
 * Get a floatval()'d version of the theme version.
 */
if (!function_exists('nmc_theme_version')) {
    function nmc_theme_version()
    {
        $theme = wp_get_theme();
        return floatval($theme->version);
    }
}

/**
 * Current User Can (Any)
 *
 * Helper function to check if a user
 * can do any of an array of capabilities.
 */
if (!function_exists('nmc_current_user_can_any')) {
    function nmc_current_user_can_any($capabilities)
    {
        if (!is_user_logged_in()) {
            return false;
        }

        if (!is_array($capabilities)) {
            if (is_string($capabilities)) {
                $capabilities = [$capabilities];
            } else {
                return false;
            }
        }

        foreach ($capabilities as $cap) {
            if (current_user_can($cap)) {
                return true;
            }
        }

        return false;
    }
}

/**
 * NMC Post Labels
 *
 * Helper function for adding all the various
 * labels that WP needs when setting up a post type.
 */
if (!function_exists('nmc_post_labels')) {
    function nmc_post_labels($singular, $plural, $slug = null)
    {
        if (!$slug) {
            $slug = get_option('stylesheet');
        }

        return [
            'name'               => _x($plural, 'post type general name', $slug),
            'singular_name'      => _x($singular, 'post type singular name', $slug),
            'menu_name'          => _x($plural, 'admin menu', $slug),
            'name_admin_bar'     => _x($singular, 'add new on admin bar', $slug),
            'add_new'            => _x('Add New', 'event', $slug),
            'add_new_item'       => __('Add New '.$singular, $slug),
            'new_item'           => __('New '.$singular, $slug),
            'edit_item'          => __('Edit '.$singular, $slug),
            'view_item'          => __('View '.$singular, $slug),
            'all_items'          => __('All '.$plural, $slug),
            'search_items'       => __('Search '.$plural, $slug),
            'parent_item_colon'  => __('Parent '.$plural.':', $slug),
            'not_found'          => __('No '.strtolower($plural).' found.', $slug),
            'not_found_in_trash' => __('No '.strtolower($plural).' found in Trash.', $slug)
        ];
    }
}

/*
    slug - required e.x. news
    singular - (defaults to capitalized slug) e.x. News Item
    plural - (defaults to singular + s) e.x. News Items
    icon - (defaults to dashicons-media-default)
    post_args - args to pass into register_post_types defaults:
        $default_post_args = [
            'labels'             => nmc_post_labels($singular, $plural),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => false,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => $slug, 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => $icon,
            'supports'           => ['title','editor']
        ];
    ---
    fields are optional, they can be done later
    fields_title - (defaults to "[Singular] Fields")
    fields - array of fields to pass to addFields

*/
if (!function_exists('nmc_register_post_type')) {
    function nmc_register_post_type($a)
    {
        $slug = $a['slug'];
        $singular = isset($a['singular']) ? $a['singular'] : ucfirst($slug);
        $plural = isset($a['plural']) ? $a['plural'] : $singular . 's';
        $icon = isset($a['icon']) ? $a['icon'] : 'dashicons-media-default';
        $fields_title = isset($a['fields_title']) ? $a['fields_title'] : $singular . ' Fields';
        $fields_group_name = isset($a['fields_group_name']) ? $a['fields_group_name'] : $slug . '_fields';
        $post_args = isset($a['post_args']) ? $a['post_args'] : [];

        $default_post_args = [
            'labels'             => nmc_post_labels($singular, $plural),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => false,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => $slug, 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => $icon,
            'supports'           => ['title','editor']
        ];

        $args = array_merge($default_post_args, $post_args);
        add_action('init', function () use ($args,$slug) {
            register_post_type($slug, $args);
        });

        if (isset($a['fields'])) {
            $fields = new \NMC_WP\Builder\FieldsBuilder($fields_group_name, ['title'=>$fields_title]);
            $fields->setLocation('post_type', '==', $slug);
            $fields->addFields($a['fields']);
            $fields->register();
        }
    };
}

/**
 * Override Better Search/Replace Capability
 *
 * By default, BSR uses 'install_plugins' to determine if a user can
 * access BSR as a plugin. However, setting DISALLOW_FILE_MODS to true
 * always sets 'install_plugins' to false, so we're replacing it with
 * the more reliable 'edit_users' capability.
 */
if (!function_exists('nmc_bsr_capability')) {
    add_filter('bsr_capability', 'nmc_bsr_capability');
    function nmc_bsr_capability()
    {
        return 'edit_users';
    }
}

/**
 * Fix Failing wp_mail()
 *
 * When using wp_mail(), Wordpress will fail due to the format of
 * $_SERVER['SERVER_NAME'] in the wp_mail_from field when setting
 * up phpmailer. This fixes this by grabbing the from field
 * from Easy WP SMTP.
 *
 * Note: You need to have Easy WP SMTP installed
 * and configured for this to work.
 */
if (!function_exists('nmc_default_wp_mail_from')) {
    add_filter('wp_mail_from', 'nmc_default_wp_mail_from');
    function nmc_default_wp_mail_from($from_email)
    {
        $swpsmtp_options = get_option('swpsmtp_options');
        return $swpsmtp_options['from_email_field'] ?? $from_email;
    }
}

/**
 * Hide Core Update Notice
 *
 * Remove the core update notice for all
 * users in the admin area.
 */
if (!function_exists('nmc_hide_core_update_notice')) {
    add_action('admin_head', 'nmc_hide_core_update_notice', 1);
    function nmc_hide_core_update_notice()
    {
        remove_action('admin_notices', 'update_nag', 3);
    }
}

/**
 * NMC Login Logo
 *
 * Optionally replace the Wordpress logo by adding
 * a login-logo.png file to the theme. Also has options
 * to change the login text color.
 */
if (!function_exists('nmc_login_logo')) {
    function nmc_login_logo()
    {
        /**
         * Apply Filter: nmc_login_logo_options
         */
        $imageOptions = apply_filters('nmc_login_logo_options', []);

        // All imagem URIs are relative to the theme.
        $imageOptions[] = '/login-logo.png';

        foreach ($imageOptions as $option) {
            $path = get_template_directory().$option;

            if (file_exists($path)) {
                list($originalWidth, $originalHeight, $type, $attr) = getimagesize($path);

                // Find the height relative to the width.
                $width = apply_filters('nmc_login_logo_width', 260);
                $height = ceil(($originalHeight * $width) / $originalWidth);

                // Button color
                $buttonColor = apply_filters('nmc_login_button_color', '#0085ba');

                $html = '<style type="text/css">
                    #login h1 a, .login h1 a {
                        background-image: url('.get_stylesheet_directory_uri().$option.');
                        height: '.$height.'px;
                        width: '.$width.'px;
                        background-size: '.$width.'px '.$height.'px;
                        background-repeat: no-repeat;
                    }
                    #wp-submit {
                        background: '.$buttonColor.';
                        border: none;
                        box-shadow: none;
                        text-shadow: none;
                    }
                </style>';
                echo $html;

                break;
            }
        }
    }
    add_action('login_enqueue_scripts', 'nmc_login_logo');
}
