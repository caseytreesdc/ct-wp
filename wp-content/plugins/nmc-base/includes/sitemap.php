<?php

/**
 * Sitemap
 *
 * Loads a basic siteamp XML
 * file at /sitemap/
 */
header('Content-Type:application/xml; charset=utf-8;');

$context = Timber::get_context();
Timber::render('0_lib/sitemap.twig', $context);
