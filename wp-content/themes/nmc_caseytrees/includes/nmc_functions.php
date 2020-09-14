<?php



function three_column_articles($cats=null,$operator='AND') {

    $args = array(
        'post_type' => array('post', 'trees', 'resources', 'page'),
        'posts_per_page' => 3
    );

    if ($cats) {
        $args['tax_query'] = array(
			array(
				'taxonomy' => 'resources-tags',
				'field'    => 'ID',
				'terms'    => array($cats),
				'operator' => $operator
			)
		);
    }

    return Timber::get_posts($args);
}



function custom_archive_list() {

	$archives = wp_get_archives(array(
		'type' => 'monthly',
		'format' => 'html',
		'show_post_count' => 0,
		'echo' => 0
	));
	$archives = explode("\n", $archives);
	$result = array();
	for($i=0; $i<sizeof($archives); $i++) {

		$label = trim(strip_tags($archives[$i]));
		$date = date('Y-m', strtotime($label));

		$result[$i]['label'] = $label;
		$result[$i]['value'] = $date;
	}
	array_pop($result);

	return $result;
}



function most_read_posts() {

	return Timber::get_posts(array(
		'posts_per_page' => 4,
		'meta_key' => 'wpb_post_views_count',
		'orderby' => 'meta_value_num',
		'order' => 'DESC'
	));
}



function get_latest_events($cats=null,$count=3) {

	// $args = array(
	// 	'post_type' => 'tribe_events',
	// 	'posts_per_page' => $count
	// );

	$time = date('Y-m-d');
	$args = array(
		'post_type' => 'tribe_events',
		'meta_key' => '_EventStartDate',
		'meta_value' => $time,
		'meta_compare' => '>=',
	    'orderby' => 'meta_value',
	    'order' => 'ASC',
		'posts_per_page' => $count
	);

	if ($cats) {
		$args['tax_query'] = array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'tribe_events_cat',
				'field'    => 'term_id',
				'terms'    => $cats,
			)
		);
	}

	return Timber::get_posts($args);
}



function get_latest_events_new($cats=null,$count=3) {

	$args = array(
		'post_type' => 'tribe_events',
		'posts_per_page' => -1
	);

	return Timber::get_posts($args);
}



function wpb_set_post_views($postID) {
    $count_key = 'wpb_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}



function nmc_remove_tags(){
    register_taxonomy('post_tag', array());
}
add_action('init', 'nmc_remove_tags');



function nmc_custom_event_button() {

	$options = get_field('button_options', $post->ID);

	if ($options) {
		$buttonOptions = $options[0];
		$type = $buttonOptions['type'];
		$linkOptions = $buttonOptions['link'];

		if ($linkOptions['type'] == 'internal') {
			$url = $linkOptions['internal_link'];
		} elseif ($linkOptions['type'] == 'external') {
			$url = $linkOptions['external_link'];
		}

		$class = 'btn green';

		if ($type['value'] != 'rsvp') {
			$class = 'btn gray';
		}

		$target = $linkOptions['new_tab'] ? ' target="_blank"' : '';
		$label = $type['label'];
	}

	$button = printf("<div class=\"event-button\"><a href=\"%s\" class=\"%s\"%s>%s</a></div>", $url, $class, $target, $label);
}
