<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * To generate specific templates for your pages you can use:
 * /mytheme/views/page-mypage.twig
 * (which will still route through this PHP file)
 * OR
 * /mytheme/page-mypage.php
 * (in which case you'll want to duplicate this file and save to the above path)
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$templates = array();

// Check for a page template that's a twig template. If this page
// has a twig template defined, trying loading that first.
if ($post->_wp_page_template and pathinfo($post->_wp_page_template, PATHINFO_EXTENSION) == 'twig') {
	$templates[] = $post->_wp_page_template;
}

$templates[] = 'page-'.$post->post_name.'.twig';
$templates[] = 'page.twig';

Timber::render($templates, $context);