<?php
/**
 * Template Name: Sitemap Page
 * Description: A Page Template for the sitemap
 */

$template = file_get_contents(__DIR__.'/templates/sitemap.twig');
$context = Timber::get_context();
header('Content-Type:application/xml; charset=utf-8;');
return Timber::render_string( $template, $context );