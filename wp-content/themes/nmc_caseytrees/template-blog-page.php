<?php
/**
/*
 * Template Name: Blog Page
 * Description: A Page Template for blog posts
 */

global $paged;
if (!isset($paged) || !$paged){
    $paged = 1;
}

$context = Timber::get_context();

$context['get'] = $_GET;

$args = array(
    'post_type' => 'post',
    'paged' => $paged
);

if ($_GET['date']) {
	$date = sanitize_text_field($_GET['date']);
	$dateArray = explode('-', $date);
	$year = $dateArray[0];
	$month = $dateArray[1];
	$args['date_query'] = array(
		'year' => $year,
		'month' => $month
	);
}

if ($_GET['category']) {
	$args['category_name'] = sanitize_text_field($_GET['category']);
}

if ($_GET['search']) {
	$args['s'] = sanitize_text_field($_GET['search']);
}

query_posts($args);

$pagination = Timber::get_pagination();

$context['posts'] = Timber::get_posts();
$context['post'] = new TimberPost();
$context['pagination'] = $pagination;

Timber::render( 'template-blog-page.twig', $context );