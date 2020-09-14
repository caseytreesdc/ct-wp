<?php

global $paged;
global $wp_query;

if (!isset($paged) || !$paged){
    $paged = 1;
}

$context = Timber::get_context();

$context['get'] = $_GET;

$wp_query->query_vars['post_type'] = array('post', 'resources', 'trees');
$wp_query->query_vars['paged'] = $paged;
$wp_query->query_vars['posts_per_page'] = 15;

if ($_GET['resource_category'] && $_GET['resource_category'] != 'null') {

	if ($_GET['resource_category'] == 'blog') {
		$wp_query->query_vars['post_type'] = 'post';
	} elseif ($_GET['resource_category'] == 'trees') {
		$wp_query->query_vars['post_type'] = 'trees';
		$wp_query->query_vars['orderby'] = 'title';
		$wp_query->query_vars['order'] = 'ASC';
	} elseif ($_GET['resource_category'] == 'pages') {
		$wp_query->query_vars['post_type'] = 'page';
		$wp_query->query_vars['orderby'] = 'title';
		$wp_query->query_vars['order'] = 'ASC';
	} else {

		$wp_query->query_vars['tax_query'] = array(
			array(
				'taxonomy' => 'resources-categories',
				'field'    => 'slug',
				'terms'    => sanitize_text_field($_GET['resource_category'])
			)
		);
	}
}

if ($_GET['search']) {
	$wp_query->query_vars['s'] = sanitize_text_field($_GET['search']);
	// relevanssi_do_query($_GET['search']);
}

$pagination = Timber::get_pagination();

print_r($wp_query->query_vars);

// if ( have_posts() ) {
// 	while ( have_posts() ) {
// 		the_post();
// 	}
// }

// $context['posts'] = Timber::get_posts();
// $context['post'] = new TimberPost();
$context['pagination'] = $pagination;

$context['resource_categories'] = Timber::get_terms(array(
	'taxonomy' => 'resources-categories'
));

Timber::render( 'template-resources.twig', $context );