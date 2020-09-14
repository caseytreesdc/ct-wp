<?php
/**
 * The template for displaying the blog listing page.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.2
 */

$context = Timber::get_context();

$context['posts'] = Timber::get_posts();
$context['pagination'] = Timber::get_pagination();

Timber::render( 'archive.twig', $context );
