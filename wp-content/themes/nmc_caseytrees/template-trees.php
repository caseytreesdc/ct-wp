<?php
/**
/*
 * Template Name: Trees Page
 */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$data = $_GET;
$context['data'] = $data;

// print_r($data)

// Choice Fields
$context['size'] = get_field_object('field_585adaf7b2ac9');
$context['light_conditions'] = get_field_object('field_585adabdb2ac4');
$context['soil_conditions'] = get_field_object('field_585adacdb2ac5');

$args = array(
	'post_type' => 'trees',
	'posts_per_page' => -1,
	'orderby' => 'title',
	'order' => 'ASC'
);

$context['all_trees'] = Timber::get_posts($args);

// Selects
// ========================
// light_conditions
// soil_conditions
// crown_form
// size

// Checkboxes
// ========================
// drought_tolerant
// air_pollution_tolerant
// salt_tolerant
// prominent_flower
// showy_seasonal_color
// fruitnut_producing
// evergreen
// native

$trees = Timber::get_posts($args);

foreach($trees as $tree) {

	$debug = false;

	// Assume the tree matches the filter
	$keep = 1;

	if ($debug) { echo 'Keep: ' . $keep . "\n"; }

	foreach ($data as $key => $value) {

		// print_r ($value);

		// Change 'on' to 1 and 'off' to 0 for simpler comparison
		if (!is_array($value)) {
			if ($value == 'on') {
				$value = 1;
			} elseif ($value == 'off') {
				$value = 0;
			}

			// Does this filter apply?
			if ($value != '' || $value != 0) {

				if ($debug) {
					echo $key . ' - ' . $value . "\n";
					echo $tree->$key . "\n";
				}

				if ($tree->$key != $value) {
					$keep = 0;
				}
			}
		} else {

			if (!in_array($tree->$key, $value)) {
				$keep = 0;
			}
		}
	}

	if ($debug) { echo 'Keep: ' . $keep . "\n"; }

	// Remove tree from results if needed
	if (!$keep) {
		$unset = array_search($tree, $trees);
		unset($trees[$unset]);
		$trees = array_values($trees);
	}

}

$context['trees'] = $trees;

Timber::render( array( 'template-trees.twig' ), $context );