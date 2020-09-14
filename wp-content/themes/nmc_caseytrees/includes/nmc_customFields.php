<?php
if( function_exists('acf_add_options_page') ) {

	acf_add_options_page();
	acf_add_options_sub_page('General');

	acf_add_local_field_group(array(
		'key' => 'group_5e6bd6b882fc6',
		'title' => 'Get Updates',
		'fields' => array(
			array(
				'key' => 'field_5e6bd77f36afb',
				'label' => 'Hide Get Updates Block',
				'name' => 'hide_get_updates',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
				'ui' => 1,
				'ui_on_text' => 'Hide',
				'ui_off_text' => 'Show',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '!=',
					'value' => 'null',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
	));

}