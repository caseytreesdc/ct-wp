<?php

class NMCSite extends \NMC_WP\Site {

	function __construct() {

        function nmc_setup () {
            remove_action('wp_head', 'wp_generator');
            remove_action('wp_head', 'wlwmanifest_link');
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'wp_shortlink_wp_head');
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
            add_filter('the_generator', '__return_false');
            add_filter('show_admin_bar','__return_false');
            remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
            remove_action( 'wp_print_styles', 'print_emoji_styles' );
            remove_action('wp_head', 'feed_links_extra', 3);
            remove_action('set_comment_cookies', 'wp_set_comment_cookies');
        }

        add_action('after_setup_theme', 'nmc_setup');

        /* Enqueues
        ========================================*/
        function is_login_page()
        {
            return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
        }

        if(!is_admin() && !is_login_page())
        {

            /* Add Styles */
            function nmcbase_enqueue_style() {
                wp_enqueue_style( 'all', get_bloginfo('stylesheet_directory') . '/css/all.min.css', array(), time() );
            }

            /* Add Scripts - Note that jQuery is already enqueued by default */
            function nmcbase_enqueue_script() {
                wp_deregister_script('jquery');
                wp_enqueue_script( 'jquery', get_bloginfo('stylesheet_directory') . '/scripts/jquery.min.js', array(), '3.1.1', true );
                wp_enqueue_script( 'site', get_bloginfo('stylesheet_directory') . '/scripts/all.min.js', array('jquery'), time(), true );
            }

            add_action( 'wp_enqueue_scripts', 'nmcbase_enqueue_style' );
            add_action( 'wp_enqueue_scripts', 'nmcbase_enqueue_script' );
        }

        /* Register twig page templates
         * These will be loaded directly from /templates
         */
        add_filter('theme_page_templates', 'add_twig_templates');
        function add_twig_templates($page_templates) {
            //$page_templates['template-about.twig'] = 'About Page';
            return $page_templates;
        }

        /* Register additional nav menu locations
         * Add them to $context in the add_to_context function below
         */
        register_nav_menus(array(
        	'primary-nav' => 'Primary Navigation',
            'utility-nav' => 'Utility Navigation',
            'footer-nav' => 'Footer Navigation',
            'footer-utility-nav' => 'Footer Utility Navigation'
        ));

        /* Image Sizes */
        //add_image_size('masthead-default', 1440, 545, true);

        /* Includes
        ========================================*/
        include('includes/nmcbase_showPostInfo.php');
        include('includes/nmc_restrictions.php');
        include('includes/nmc_subnav.php');
        include('includes/nmc_customFields.php');
        include('includes/nmc_wrap_content.php');
        include('includes/nmc_masthead.php');
        include('includes/nmc_trees.php');
        include('includes/nmc_resources.php');
        include('includes/nmc_social.php');
        include('includes/nmc_functions.php');

        remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

        show_admin_bar(false);

        /* Other Theme Support
        ========================================*/
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );
        add_theme_support( 'menus' );

		add_filter( 'timber_context', array( $this, 'add_to_context' ) );

		parent::__construct();
	}

    /* Add to $context to make available throughout site */
	function add_to_context( $context ) {

        if (has_nav_menu('primary-nav')) {
            $context['primary_nav'] = new TimberMenu('primary-nav');
        }

        if (has_nav_menu('utility-nav')) {
            $context['utility_nav'] = new TimberMenu('utility-nav');
        }

        if (has_nav_menu('footer-nav')) {
            $context['footer_nav'] = new TimberMenu('footer-nav');
        }

        if (has_nav_menu('footer-utility-nav')) {
            $context['footer_utility_nav'] = new TimberMenu('footer-utility-nav');
        }

		$context['images'] = get_bloginfo('stylesheet_directory') . '/assets/';
		$context['site'] = $this;

		if (get_fields('options')) {
            $context['options'] = get_fields('options');
        }

		return $context;
	}
}

new NMCSite();
