<?php

namespace NMC_WP;

use Routes;
use Timber;
use TimberSite;
use TimberMenu;

class Site extends TimberSite
{
    protected $cdn_secret_key;
    protected $asset_version;
    protected $is_wp_admin = false;
    protected $templates = [];
    protected $timber_context = [];
    protected $acf_admins = [];
    protected $twig_global;

    protected $excluded_search_blocks = [];
    protected $excluded_search_fields = ['acf_fc_layout'];

    /**
     * Twig Global Variable
     *
     * The variable we are going to bind our
     * custom Twig functions and variables to. Defaults
     * to wp, which would be called via {{ wp }}
     * in Twig templates.
     *
     * @var string
     */
    protected $twig_global_var = 'wp';

    /**
     * __construct()
     */
    public function __construct()
    {
        include __DIR__.'/../includes/core-customizations.php';

        /**
         * Set Template Directories
         */
        $templateDirectories = [
            get_stylesheet_directory().'/templates',
            WP_PLUGIN_DIR.'/nmc-base/templates'
        ];

        Timber::$locations = apply_filters('nmc_templates_directories', $templateDirectories);

        /**
         * Autoescape
         *
         * Unless we are specifically forcing autoescaping via
         * a filter, autoescaping is only turned on for theme
         * version 5.0 and above.
         */
        $autoescape = apply_filters('nmc_timber_autoescape_override', null);
        if (!is_null($autoescape)) {
            Timber::$autoescape = $autoescape;
        } else {
            Timber::$autoescape = (nmc_theme_version() >= 5.0) ? 'html' : false;
        }

        $this->asset_version = file_get_contents(get_theme_root().'/'.get_option('stylesheet').'/templates/_version.num');

        $this->is_wp_admin = is_admin() || in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php']);

        $this->twig_global = new TwigGlobal();

        parent::__construct();

        $this->initialNMCCustomizations();
        $this->initialize();
        $this->initRoutes();
        $this->deferScripts();
        $this->finalNMCCustomizations();
        $this->commitTemplates();
        $this->commitContext();

        // Load CDN secret key
        $key_file = '/etc/nmc/cdn_secret_key.txt';
        if (is_readable($key_file)) {
            $this->cdn_secret_key = trim(file_get_contents($key_file));
        } else {
            $this->cdn_secret_key = 'MISSING'; // <-- This will purposefully break images that use the new resizer
        }

        /**
         * Twig Customizations
         */
        add_filter('timber/twig', function ($twig) {

            // Add our twig global variable
            $twig->addGlobal($this->getTwigGlobalVariable(), $this->twig_global);

            /**
             * Image Resizing Filters
             */
            $acf_options = get_fields('options');
            $url_maker = new \NMC_WP\Cdn\Url('https://e1.nmcdn.io', $this->cdn_secret_key);
            $url_adapter = new \NMC_WP\Cdn\Adapter(
                $url_maker,
                $acf_options['cdn_asset_version'] ?? '1',
                $acf_options['cdn_disable'] ?? false
            );
            $twig->addFilter(new \Twig_SimpleFilter('imageresize', [$url_adapter, 'imageresize']));
            $twig->addFilter(new \Twig_SimpleFilter('imagesize', [$url_adapter, 'imageresize']));

            /**
             * Sanitizing and Escaping Filters
             */
            $sanitizer = new \NMC_WP\Sanitize;
            $twig->addFilter(new \Twig_SimpleFilter('wp_sanitize', [$sanitizer, 'wordpressSanitize']));
            $twig->addFilter(new \Twig_SimpleFilter('wp_sanitize_array', [$sanitizer, 'wordpressSanitizeArray']));
            $twig->addFilter(new \Twig_SimpleFilter('wp_sanitize_slug_array', [$sanitizer, 'wordpressSanitizeSlugArray']));
            $twig->addFilter(new \Twig_SimpleFilter('wp_force_number', [$sanitizer, 'forceNumber']));

            /**
             * Custom Tags
             */
            $twig->addTokenParser(new \NMC_WP\Retrieve\RetrieveTokenParser());

            return $twig;
        });
    }

    /**
     * Initialize
     *
     * Used by the site class in functions.php
     * to customize site settings and behaviors.
     */
    protected function initialize()
    {
        return;
    }

    /**
     * Swt Twig Global Variable
     *
     * @param string $variable
     */
    public function setTwigGlobalVariable($variable)
    {
        $this->twig_global_var = $variable;
    }

    /**
     * Get Twig Global Variable
     *
     * @return string
     */
    public function getTwigGlobalVariable()
    {
        return $this->twig_global_var;
    }

    /**
     * Init Routes
     *
     * Initialize routes that are always-present
     * on NMC sites.
     *
     * @return void
     */
    public function initRoutes()
    {
        // Sitemap route - theme version 5.0 and up
        $override = apply_filters('nmc_legacy_theme_sitemap_override', false);
        if ($override === true or nmc_theme_version() >= 5.0) {
            $sitemap_route = apply_filters('nmc_sitemap_route', 'sitemap');
            Routes::map($sitemap_route, function ($params) {
                Routes::load(__DIR__.'/../includes/sitemap.php');
            });
        }
    }

    /**
     * Include
     *
     * Includes a file in the template directory.
     * Throws an exception if that file cannot
     * be found.
     *
     * @param  string $path
     * @return void
     */
    private function include($path)
    {
        $fullPath = get_template_directory().$path;

        if (file_exists($fullPath)) {
            include($fullPath);
        } else {
            throw new \Exception('Unable to find file '.$fullPath);
        }
    }

    /**
     * Add Custom Fields
     *
     * Include an array of custom field files
     * to be included as part of the theme.
     *
     * @param    mixed $arr
     * @return   void
     */
    protected function addCustomFields($arr)
    {
        if (!is_array($arr)) {
            $arr = [$arr];
        }

        foreach ($arr as $fieldset) {
            $this->include('/content-structure/fields/' . $fieldset . '.php');
        }
    }

    /**
     * Add Post Type
     *
     * @param   string $type
     * @return  void
     */
    protected function addPostType($type)
    {
        $this->addPostType([$type]);
    }


    /**
     * Add Post Types
     *
     * @param   array $arr
     * @return  void
     */
    protected function addPostTypes($arr)
    {
        foreach ($arr as $type) {
            $this->include('/content-structure/types/' . $type . '.php');
        }
    }

    /**
     * Add Template
     *
     * Add a template in a variety of formats 
     * to Wordpress' available template list.
     *
     * @param   mixed $template
     * @param   void
     */
    protected function addTemplate($template)
    {
        if (is_string($template)) {

            // This was called like addTemplate('my-template.twig').
            // We have to use the path for both data points.
            $this->templates = array_merge($this->templates, [$template => $template]);

        } elseif (is_array($template)) {

            $key = array_key_first($template);
            $value = reset($template);
            
            // Numeric key means that this was just an array
            // of template names, without the key => value distinction.
            if (is_numeric($key)) {
                $this->templates = array_merge($this->templates, [$value => $value]);
            } else {
                $this->templates = array_merge($this->templates, [$key => $value]);
            }
        }
    }

    /**
     * Add Templates
     *
     * @param array $templates
     */
    protected function addTemplates($templates)
    {
        foreach ($templates as $key => $name) {
            $this->addTemplate([$key => $name]);
        }
    }

    /**
     * Add Menu
     *
     * @param   string $menuKey
     * @param   string $menuName
     * @return  void
     */
    protected function addMenu($menuKey, $menuName)
    {
        $this->addMenus([$menuKey => $menuName]);
    }

    /**
     * Add Menus
     *
     * @param array $arr
     */
    protected function addMenus($arr)
    {
        register_nav_menus($arr);

        foreach ($arr as $key => $val) {

            // Dashes don't work in Timber
            $contextKey = str_replace('-', '_', $key);

            $this->addToContext($contextKey, function () use ($key) {
                return new TimberMenu($key);
            });
        }
    }

    /**
     * Provide gutenberg block whitelist
     *
     * @param array $arr
     */
    protected function whitelistBlockTypes($arr)
    {
        add_filter('allowed_block_types', function ($allowed_block_types, $post) use ($arr) {
            return $arr;
        }, 11, 2);
    }

    /**
     * At category names to gutenberg
     *
     * @param array $arr  [slug=>title]
     */
    protected function addBlockCategory($slug, $title)
    {
        $this->addBlockCategories([$slug=>$title]);
    }
    protected function addBlockCategories($arr)
    {
        add_filter('block_categories', function ($categories, $post) use ($arr) {
            $formatted_arr = [];
            foreach ($arr as $slug => $title) {
                $formatted_arr[] = [
                    'slug'=>$slug,
                    'title'=> __($title, $slug . '-blocks')
                ];
            }
            return array_merge(
                $categories,
                $formatted_arr
            );
        }, 10, 2);
    }

    /**
     * Add stylesheets to gutenberg
     *
     * @param String $slug unique name for the sheet
     * @param String $file a file in the public css folder
     */
    protected function addGutenbergStylesheet($slug, $file)
    {
        add_action('enqueue_block_editor_assets', function () use ($slug,$file) {
            wp_enqueue_style(
                $slug,
                get_stylesheet_directory_uri() . "/css/" . $file,
                [],
                $this->asset_version
            );
        });
    }

    /**
     * Add ACF Admin
     *
     * @param   string $admin
     * @return  void
     */
    protected function addACFAdmin($username)
    {
        $this->addACFAdmins([$username]);
    }

    /**
     * Add ACF Admins
     *
     * Pass an array of usernames that should
     * be able to see the ACF admin interface.
     *
     * @param   array $admins
     * @return  void
     */
    protected function addACFAdmins($usernames)
    {
        $this->acf_admins = array_merge($this->acf_admins, $usernames);
    }

    /**
     * Add to Context
     *
     * Accepts either an array of key => val pairs,
     * or ($key, $val). Value can be an array, string, etc,
     * or it can be a callable, which will be executed
     * at the time that the context is committed.
     *
     * @return  void
     */
    protected function addToContext()
    {
        if (func_num_args() == 1) {
            $this->timber_context = array_merge($this->timber_context, func_get_arg(0));
        } else {
            $this->addToContext([func_get_arg(0) => func_get_arg(1)]);
        }
    }

    /**
     * Commit Context
     *
     * Run through our aggregated context and
     * either add that data or run the callbacks.
     *
     * @return  void
     */
    private function commitContext()
    {
        $localContext = $this->timber_context;

        add_filter('timber_context', function ($context) use ($localContext) {
            foreach ($localContext as $key => $item) {
                if (is_callable($item)) {
                    $context[$key] = $item();
                } else {
                    $context[$key] = $item;
                }
            }

            return $context;
        });
    }

    /**
     * Remove Menu Pages
     *
     * @param  array $removals
     */
    protected function removeMenuPages($removals)
    {
        add_action('admin_menu', function () use ($removals) {
            foreach ($removals as $page) {
                if (strpos($page, '.php') === false) {
                    $page .= '.php';
                }
                remove_menu_page($page);
            }
        }, 999);
    }

    /**
     * Add WP Var
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    protected function addWpVar($key, $value)
    {
        $this->addWpVars([$key => $value]);
    }

    /**
     * Add WP Vars
     *
     * @param   array $arr
     * @return  void
     */
    protected function addWpVars($arr)
    {
        $this->twig_global->addVars($arr);
    }

    /**
     * Add WP Method
     *
     * @param   string $name
     * @param   callable $fn
     * @return  void
     */
    protected function addWpMethod($name, $fn)
    {
        $this->addWpMethods([$name => $fn]);
    }

    /**
     * Add WP Methods
     *
     * @param   array $methods
     * @return  void
     */
    protected function addWpMethods($arr)
    {
        foreach ($arr as $name => $fn) {
            $this->twig_global->addMethod($name, $fn);
        }
    }

    /**
     * Commit Templates
     */
    private function commitTemplates()
    {
        $templates = $this->templates;
        add_filter('theme_page_templates', function ($page_templates) use ($templates) {
            return array_merge($page_templates, $templates);
        });
    }

    /**
     * Default Customizations
     *
     * Core set of Wordpress customizations
     * that happen on every site.
     */
    protected function finalNMCCustomizations()
    {
        $base_settings = get_option('nmc_base_settings');

        // Show admin bar, unless we've decided to hide it.
        if (apply_filters('show_admin_bar', true)) {
            $this->adminBarInit();
        }

        $this->initEnqueuedScripts();

        // Load options out of a transient for performance
        if (false === ($options = get_transient('nmc_options_cache'))) {
            $options = get_fields('options');
            set_transient('nmc_options_cache', $options, apply_filters('nmc_options_cache_ttl', 60 * 60 * 24));
        }

        // Clear options transient on acf options save
        add_action('acf/save_post', function ($post_id) {
            if ($post_id == 'options') {
                delete_transient('nmc_options_cache');
            }
        }, 20);

        /**
         * Add Basic Context Items
         */

        $context = [
            'assets'    => get_bloginfo('stylesheet_directory').'/assets/',
            'options'   => $options
        ];

        // NMC Social keys
        // Backwards compat - if there is an old nmc_social_settings
        // API key, then use that.
        if (!isset($base_settings['api_key']) or !$base_settings['api_key']) {
            $social_options = get_option('nmc_social_settings');
            $base_settings['api_key'] = $social_options['api_key'] ?? null;
        }

        if (isset($base_settings['api_key']) and $base_settings['api_key']) {
            $key = $base_settings['api_key'];
            $cacheDir = $_SERVER['DOCUMENT_ROOT'].'/wp-content/cache/nmc-social';
            $context['social'] = new \NMC_Social\TimberInterface($key, $cacheDir);
        }

        $this->addToContext(apply_filters('nmc_default_context', $context));

        /**
         * ACF Admin Menu Show/Hide
         */
        $admins = $this->acf_admins;
        add_filter('acf/settings/show_admin', function () use ($admins) {
            return in_array(wp_get_current_user()->user_login, $admins);
        });

        /**
         * Varnish Cache Clearing
         */
        $this->purger = new \NMC_WP\Cache\Purger();
        $strategySetting = $base_settings['caching_strategy'] ?? null;

        if ($strategySetting == 'performance') {
            $this->purger->addStrategy(new \NMC_WP\Cache\Strategy\Ladder());
            $this->purger->addStrategy(new \NMC_WP\Cache\Strategy\ImportantUrls());
            $this->purger->addStrategy(new \NMC_WP\Cache\Strategy\Attachments());
        } else {
            $this->purger->addStrategy(new \NMC_WP\Cache\Strategy\EntireSite());
        }

        $purger = $this->purger;

        add_action('save_post', function ($post_id, $post, $is_update) use ($purger) {
            $purger->purge($post);
        }, 10, 3);
    }

    /**
     * Init Enqueud Scripts
     *
     * Setup {{ wp.enqueuedScripts([]) }} function,
     * which will allow for specific script groups to be
     * displayed without dependencies. Should be used
     * with caution.
     *
     * @return void
     */
    public function initEnqueuedScripts()
    {
        $this->addWpMethod('getEnqueuedScriptData', function ($allowed) {
            $allScripts = wp_scripts();
            $scripts = [];

            foreach ($allowed as $allowedKey) {
                if (isset($allScripts->registered[$allowedKey])) {
                    $scripts[] = $allScripts->registered[$allowedKey];
                }
            }

            return $scripts;
        });

        $this->addWpMethod('enqueuedScripts', function ($allowed) {

            // Remove dependencies from the current
            // enqueued scripts
            $scripts = wp_scripts();

            foreach ($allowed as $allowedKey) {
                if (isset($scripts->registered[$allowedKey])) {
                    $scripts->registered[$allowedKey]->deps = [];
                }
            }

            wp_print_scripts($allowed);
        });
    }

    /**
     * Admin Bar Init
     *
     * Initialize the WP admin bar
     * for logged in users. Bypasses the need for
     * wp_head() and wp_footer(). Should be called
     * with wp.adminBar() after the opening
     * body tag.
     *
     * @return void
     */
    public function adminBarInit()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $this->addWpMethod('adminBar', function () {

            $version = get_bloginfo('version');
            echo "<link rel='stylesheet' id='dashicons-css'  href='/wp-includes/css/dashicons.min.css?ver={$version}' type='text/css' media='all' />
        <link rel='stylesheet' id='admin-bar-css'  href='/wp-includes/css/admin-bar.min.css?ver={$version}' type='text/css' media='all' />
        <script src='/wp-includes/js/admin-bar.min.js?ver={$version}' defer='defer' type='text/javascript'></script>";

            wp_admin_bar_render();
        });
    }

    /**
     * Establish NMC Defaults
     */
    protected function initialNMCCustomizations()
    {
        // Defaults
        $this->addACFAdmin('nmcteam');
        $this->removeMenuPages(['edit-comments']);
    }

    /**
     * Enqueue Styles
     * Legacy: this doesn't appear to be used anymore.
     */
    protected function enqueue_styles($styles)
    {
        if (!$this->is_wp_admin) {
            $version = $this->asset_version;
            add_action('wp_enqueue_scripts', function () use ($styles,$version) {
                foreach ($styles as $file) {
                    wp_enqueue_style('all', get_bloginfo('stylesheet_directory') . '/css/' . $file, [], $version);
                }
            });
        }
    }

    /**
     * Enqueue Scripts
     *
     * Legacy: this doesn't appear to be used anymore.
     */
    protected function enqueue_scripts($scripts)
    {
        if (!$this->is_wp_admin) {
            $version = $this->asset_version;
            add_action('wp_enqueue_scripts', function () use ($scripts,$version) {
                foreach ($scripts as $file) {
                    wp_enqueue_script('site', get_bloginfo('stylesheet_directory') . '/scripts/' . $file, ['jquery'], $version);
                }
            });
        }
    }

    /**
     * Defer Scripts
     *
     * Add defer to every script that is loaded
     * via the enqueue scripts process.
     *
     * @return void
     */
    private function deferScripts()
    {
        if (apply_filters('nmc_disable_defer_scripts', false) === true) {
            return;
        }

        if (!$this->is_wp_admin) {
            add_filter('script_loader_tag', function ($tag, $handle, $src) {
                return '<script src="' . $src . '" defer="defer" type="text/javascript"></script>' . "\n";
            }, 10, 3);
        }
    }
}
