<?php



// Remove Customize Option
function remove_customize_page(){
    global $submenu;
    unset($submenu['themes.php'][6]);
}
add_action( 'admin_menu', 'remove_customize_page');




// Restrict ACF Access
function my_acf_show_admin($show) {
	// provide a list of usernames who can edit custom field definitions here
	$admins = array(
		'nmcteam'
	);

	// get the current user
	$current_user = wp_get_current_user();

	return (in_array($current_user->user_login, $admins));
}
add_filter('acf/settings/show_admin', 'my_acf_show_admin');