<?php
/**
 * The Template for displaying all single posts
 *
 *
 * @package  WordPress
 * @subpackage  Timber
 */

$context = array();
$context['post'] = Timber::get_post();
Timber::render('components/sidebar.twig', $context);