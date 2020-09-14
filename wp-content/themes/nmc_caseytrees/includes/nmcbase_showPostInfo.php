<?php
function nmcbase_showPostInfo(){

	global $wp_query;

	echo '<ul>';
	if(is_home()){ echo '<li>Type: Home</li>'; }
	if(is_front_page()){ echo '<li>Type: Front page</li>'; }
	if(is_single()){ echo '<li>Type: Single</li>'; }
	if(is_archive()){ echo '<li>Type: Archive</li>'; }
	if(is_page()){ echo '<li>Type: Page</li>'; }
	if(is_tag()){ echo '<li>Type: Tag</li>'; }
	if(is_tax()){ echo '<li>Type: Taxonomy</li>'; }
	if(is_search()){ echo '<li>Type: Search</li>'; }
	if(is_404()){ echo '<li>Type: 404</li>'; }
	if(is_singular()){ echo '<li>Type: Singular</li>'; }
	if(is_feed()){ echo '<li>Type: Feed</li>'; }
	echo '<li>Post ID: ' . $wp_query->post->ID . '</li>';
	echo '<li>Post Type: ' . get_post_type() . '</li>';
	if(is_page()){ echo '<li>Template: ' . get_post_meta( get_the_ID(), '_wp_page_template', true ) . '</li>'; }
	echo '</ul>';
	echo '<h3>Post Meta</h3>';
	print_r(get_post_meta(get_the_ID()));
	echo '<h3>WP_Query</h3>';
	print_r($wp_query);
}
?>