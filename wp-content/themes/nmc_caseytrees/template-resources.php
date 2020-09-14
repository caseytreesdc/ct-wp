<?php
/**
/*
 * Template Name: Resources Page
 */

global $paged;
if (!isset($paged) || !$paged){
    $paged = 1;
}


$context = Timber::get_context();

$context['get'] = $_GET;

$args = array(
    'post_type' => array('page', 'post', 'resources', 'trees'),
    'ignore_custom_sort' => true,
    'paged' => $paged,
    'posts_per_page' => 15
);

if ($_GET['search']) {
	$args['s'] = sanitize_text_field($_GET['search']);
}

if ($_GET['resource_category'] && $_GET['resource_category'] != 'null') {

	if ($_GET['resource_category'] == 'blog') {
		$args['post_type'] = 'post';
	} elseif ($_GET['resource_category'] == 'trees') {
		$args['post_type'] = 'trees';
		$args['orderby'] = 'title';
		$args['order'] = 'ASC';
	} elseif ($_GET['resource_category'] == 'pages') {
		$args['post_type'] = 'page';
		$args['orderby'] = 'title';
		$args['order'] = 'ASC';
	} else {

		$args['tax_query'] = array(
			array(
				'taxonomy' => 'resources-categories',
				'field'    => 'slug',
				'terms'    => sanitize_text_field($_GET['resource_category'])
			)
		);
	}
}

$args['meta_query'] = array(
	'relation' => 'OR',
	array(
		'key' => 'no_search',
		'compare' => 'NOT EXISTS'
	),
	array(
		'key' => 'no_search',
		'value' => '0',
		'compare' => '='
	)
);

// print_r($args); die;

query_posts($args);

$pagination = Timber::get_pagination();

$context['posts'] = Timber::get_posts();
$context['post'] = new TimberPost();
$context['pagination'] = $pagination;

$context['resource_categories'] = Timber::get_terms(array(
	'taxonomy' => 'resources-categories',
	'exclude' => '63'
));

Timber::render( 'template-resources.twig', $context );