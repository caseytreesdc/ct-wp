<?php

namespace NMC_WP;

use Timber;
use NMC_WP\PostQuery;

/**
 * Twig Global
 *
 * Class that is bound to a twig global variable in
 * Wordpress, typically "wp". All of the functions and variables
 * in this class will be accessible in twig.
 */
class TwigGlobal
{
    /**
     * HTTP
     *
     * @var array
     */
    public $http = [];

    /**
     * Custom Vars
     *
     * @var array
     */
    private $customVars = [];

    /**
     * Custom Methods
     *
     * @var array
     */
    private $customMethods = [];

    /**
     * __construct
     */
    public function __construct()
    {
        $this->http['get'] = $_GET;
    }

    /**
     * Get
     *
     * @param  array $args
     * @return \NMC_WP\PostQuery
     */
    public function get($args)
    {
        $query = new PostQuery($args);

        /**
         * Set Pagination
         *
         * Set up our pagination format. nmc_pagination_format can be
         * used to change the global default format.
         */
        $paginationFormat = apply_filters('nmc_pagination_format', '?{{ pageParam }}=%#%');
        $query->setPagination(['format' => str_replace('{{ pageParam }}', $query->getPageParameter(), $paginationFormat)]);

        return $query;
    }

    /**
     * Get Terms
     *
     * @param  array $args
     * @return array
     */
    public function getTerms($args)
    {
        return Timber::get_terms($args);
    }

    // Deprecated format
    public function get_terms($args)
    {
        return $this->getTerms($args);
    }

    /**
     * Debug
     *
     * @param  mixed $var
     * @return string
     */
    public function debug($var)
    {
        return '<pre>' . htmlspecialchars(json_encode($var, JSON_PRETTY_PRINT)) . '</pre>';
    }

    /**
     * Add Vars
     *
     * @param array $arr
     */
    public function addVars($arr)
    {
        $this->customVars = array_merge($this->customVars, $arr);
    }

    /**
     * Add Method
     *
     * @param string $name
     * @param callable $fn
     */
    public function addMethod($name, $fn)
    {
        $this->customMethods[$name] = $fn;
    }

    /**
     * Lazy Images
     *
     * Source: https://wordpress.stackexchange.com/questions/60792/replace-image-attributes-for-lazyload-plugin-data-src
     *
     * Find img elements in a string of HTML and add the
     * lazyimage class to each one.
     *
     * @param  string $content
     * @param  string $klass
     * @return string
     */
    public function lazyImages($content, $klass = "lazyimage")
    {
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
        $dom = new \DOMDocument();
        @$dom->loadHTML($content);

        foreach ($dom->getElementsByTagName('img') as $node) {
            $oldsrc = $node->getAttribute('src');
            $oldsrcset = $node->getAttribute('srcset');
            $oldsizes = $node->getAttribute('sizes');

            $node->setAttribute("data-src", $oldsrc);
            $node->setAttribute("data-srcset", $oldsrcset);
            $node->setAttribute("data-sizes", $oldsizes);

            $node->removeAttribute("src");
            $node->removeAttribute("srcset");
            $node->removeAttribute("sizes");

            $node->setAttribute('class', $klass." ".$node->getAttribute('class'));
        }

        $newHtml = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace(array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $dom->saveHTML()));

        return $newHtml;
    }

    /**
     * Inline CSS
     *
     * @param  string $filename
     * @return string
     */
    public function inlineCSS($filename = 'all.min.css')
    {
        return file_get_contents(get_theme_root().'/'.get_option('stylesheet').'/css/'.$filename);
    }

    /**
     * Block Styles
     *
     * Compile all blocks for a given post,
     * and return the block styles
     * compiled as a string.
     *
     * return string
     */
    public function blockStyles($post)
    {
        $post = ($post) ? $post : get_post();
        $files = [];

        // Get a list of blocks and their files
        if (\has_blocks($post->post_content)) {
            $blocks = parse_blocks($post->post_content);
            foreach ($blocks as $block) {
                if (substr($block['blockName'], 0, 4) == 'acf/') {
                    $blockSlug = substr($block['blockName'], 4);

                    // Build the path to the style
                    $blockStylePath = get_template_directory().'/css/blocks/'.$blockSlug.'.min.css';
                    
                    if (file_exists($blockStylePath)) {
                        $files[] = $blockStylePath;
                    }
                }
            }
        }

        if (!$files) {
            return null;
        }

        $files = array_unique($files);

        $cssString = null;
        foreach ($files as $file) {
            $cssString .= @file_get_contents($file);
        }

        return $cssString;
    }

    /**
     * __get
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->customVars)) {
            return $this->customVars[$name];
        }

        return false;
    }

    /**
     * __call
     *
     * @param  string $name
     * @param  array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        if (array_key_exists($name, $this->customMethods)) {
            return call_user_func_array($this->customMethods[$name], $args);
        }

        return false;
    }

    /**
     * __isset
     *
     * Let twig know that our properties are set
     *
     * @param  string  $name
     * @return boolean
     */
    public function __isset($name)
    {
        if (isset($this->customVars[$name]) or isset($this->customMethods[$name])) {
            return true;
        }

        return false;
    }
}
