<?php
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array (
	'key' => 'group_55d4dd886d9e8',
	'title' => 'Subnavigation',
	'fields' => array (
		array (
			'key' => 'field_55d4bc802c18b',
			'label' => 'Show Subnav',
			'name' => 'show_subnav',
			'type' => 'true_false',
			'instructions' => 'Check to display or inherit subnav for this page.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 0,
		),
		array (
			'key' => 'field_55d4dd901b29a',
			'label' => 'Inherit Subnav',
			'name' => 'inherit_subnav',
			'type' => 'true_false',
			'instructions' => 'Leave checked to inherit subnav from parent, grandparent, etc. Uncheck to choose subnav for this page.',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_55d4bc802c18b',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 1,
		),
		array (
			'key' => 'field_55d4ddab3b211',
			'label' => 'Subnav',
			'name' => 'subnav',
			'type' => 'nav_menu',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_55d4bc802c18b',
						'operator' => '==',
						'value' => '1',
					),
					array (
						'field' => 'field_55d4dd901b29a',
						'operator' => '!=',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'save_format' => 'object',
			'container' => 'nav',
			'allow_null' => 1,
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'page',
			),
			array (
				'param' => 'page_template',
				'operator' => '!=',
				'value' => 'template-home.php',
			),
		),
	),
	'menu_order' => 1,
	'position' => 'side',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));

endif;



/**
 * nmc_get_subnav function.
 *
 * @access public
 * @param int $id - optional, defaults to current post
 * @return string - sidebar ul markup
 */
function nmc_get_subnav($id){

    if (isset($id) && $id != '') {
        $source = $id;

        // Change source for custom post type singles
        if (get_post_type() != 'page') {
            $label = get_post_type() . '_parent';
            $source = get_field($label, 'option');
        }
    } else {
        $source = $post->ID;
    }

    if (get_field('show_subnav', $source) == true){
	    
	    $ancestors = get_post_ancestors($source);
	    if(get_field('inherit_subnav', $source) == true){
	        for($i=0; $i<=sizeof($ancestors); $i++){
	            if(get_field('inherit_subnav', $ancestors[$i]) != true && get_field('subnav', $ancestors[$i]) != ''){
	                $source = $ancestors[$i];
	                break;
	            }
	        }
	    }
	    $subnavObj = get_field('subnav', $source);

	    if ($subnavObj) {
		    $subnav = array();
		    $subnav['name'] = $subnavObj->name;
		    $subnav['menu'] = new TimberMenu($subnavObj->ID);
	    }

	    return $subnav;
	}
}
