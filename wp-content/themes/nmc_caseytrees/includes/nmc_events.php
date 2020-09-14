<?php

/* Events Post Type */
add_action( 'init', 'events_init' );
function events_init() {
	$labels = array(
		'name'               => _x( 'Events', 'post type general name', 'your-plugin-textdomain' ),
		'singular_name'      => _x( 'Event', 'post type singular name', 'your-plugin-textdomain' ),
		'menu_name'          => _x( 'Events', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Event', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add New', 'event', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Event', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Event', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Event', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Event', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Events', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Events', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Events:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No events found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No events found in Trash.', 'your-plugin-textdomain' )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => false,
		'query_var'          => true,
		'rewrite'            => array('slug' => 'event-list', 'with_front' => false),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'menu_icon'          => 'dashicons-calendar-alt',
		'supports'           => array('title', 'editor')
	);

	register_post_type( 'events', $args );
}


if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array (
	'key' => 'group_55c7bb2520842',
	'title' => 'Event Options',
	'fields' => array (
		array (
			'key' => 'field_55c7bb2f77609',
			'label' => 'Event Start Date',
			'name' => 'event_start_date',
			'type' => 'date_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'display_format' => 'F j, Y',
			'return_format' => 'Ymd',
			'first_day' => 1,
		),
		array (
			'key' => 'field_55c7bbfbcc2dc',
			'label' => 'Event Start Hour',
			'name' => 'event_start_hour',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_55c7be2c0555b',
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
			'choices' => array (
				'01' => '01',
				'02' => '02',
				'03' => '03',
				'04' => '04',
				'05' => '05',
				'06' => '06',
				'07' => '07',
				'08' => '08',
				'09' => '09',
				10 => 10,
				11 => 11,
				12 => 12,
				13 => 13,
				14 => 14,
				15 => 15,
				16 => 16,
				17 => 17,
				18 => 18,
				19 => 19,
				20 => 20,
				21 => 21,
				22 => 22,
				23 => 23,
				24 => 24,
			),
			'default_value' => array (
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'ajax' => 0,
			'placeholder' => '',
			'disabled' => 0,
			'readonly' => 0,
		),
		array (
			'key' => 'field_55c7bc31cc2dd',
			'label' => 'Event Start Minute',
			'name' => 'event_start_minute',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_55c7be2c0555b',
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
			'choices' => array (
				'00' => '00',
				15 => 15,
				30 => 30,
				45 => 45,
			),
			'default_value' => array (
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'ajax' => 0,
			'placeholder' => '',
			'disabled' => 0,
			'readonly' => 0,
		),
		array (
			'key' => 'field_55c7bbe87760a',
			'label' => 'Event End Date',
			'name' => 'event_end_date',
			'type' => 'date_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'display_format' => 'F j, Y',
			'return_format' => 'Ymd',
			'first_day' => 1,
		),
		array (
			'key' => 'field_55c7bde97e618',
			'label' => 'Event End Hour',
			'name' => 'event_end_hour',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_55c7be2c0555b',
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
			'choices' => array (
				'01' => '01',
				'02' => '02',
				'03' => '03',
				'04' => '04',
				'05' => '05',
				'06' => '06',
				'07' => '07',
				'08' => '08',
				'09' => '09',
				10 => 10,
				11 => 11,
				12 => 12,
				13 => 13,
				14 => 14,
				15 => 15,
				16 => 16,
				17 => 17,
				18 => 18,
				19 => 19,
				20 => 20,
				21 => 21,
				22 => 22,
				23 => 23,
				24 => 24,
			),
			'default_value' => array (
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'ajax' => 0,
			'placeholder' => '',
			'disabled' => 0,
			'readonly' => 0,
		),
		array (
			'key' => 'field_55c7bdfd2e38e',
			'label' => 'Event End Minute',
			'name' => 'event_end_minute',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_55c7be2c0555b',
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
			'choices' => array (
				'00' => '00',
				15 => 15,
				30 => 30,
				45 => 45,
			),
			'default_value' => array (
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'ajax' => 0,
			'placeholder' => '',
			'disabled' => 0,
			'readonly' => 0,
		),
		array (
			'key' => 'field_55c7be2c0555b',
			'label' => 'All Day?',
			'name' => 'all_day',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 1,
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'events',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));

endif;


/**
 * nmc_fullcalendar_events function.
 *
 * Advanced Custom Fields required with following fields
 *
 * event_start_date - Date Picker
 * event_start_hour - Select
 * event_start_min - Select
 * event_end_date - Date Picker
 * event_end_hour - Select
 * event_end_min - Select
 * all_day - True/False
 *
 * @access public
 * @param string $post_type
 * @return string (JSON)
 */
function nmc_fullcalendar_events($post_type)
{
    /*
        If this were to be made into a plugin, steps would be:

        * Check for Advanced Custom Fields
        * Create events post type
        * Add required fields (event_start_date, event_start_hour, event_start_min, event_end_date, event_end_hour, event_end_min, all_day)
        * Include this function within plugin, possibly others.
    */

    $today = date('Ymd');
    $events = get_posts(array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'meta_key' => 'event_start_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    ));

    $calendar_events = array();

    foreach ($events as $event) {

        // Start
        $start = get_field('event_start_date', $event->ID);
        $start_y = substr($start, 0, 4);
        $start_m = substr($start, 4, 2);
        $start_d = substr($start, 6, 2);
        $start_time = strtotime("{$start_d}-{$start_m}-{$start_y}");

        $calendar_start = date('Y-m-d', $start_time);

        // End
        $end = get_field('event_end_date', $event->ID);
        $end_y = substr($end, 0, 4);
        $end_m = substr($end, 4, 2);
        $end_d = substr($end, 6, 2);
        $end_time = strtotime("{$end_d}-{$end_m}-{$end_y}");

        $calendar_end = date('Y-m-d', $end_time);

        $all_day = get_field('all_day', $event->ID);

        if (!$all_day) {
            $calendar_start .= 'T' . get_field('event_start_hour', $event->ID) . ':' . get_field('event_start_minute', $event->ID) . ':00';
            $calendar_end .= 'T' . get_field('event_end_hour', $event->ID) . ':' . get_field('event_end_minute', $event->ID) . ':00';
        }

        // See http://fullcalendar.io/docs/event_data/events_array/ for fullcalendar.js formatting
        $this_event = array(
            'id' => $event->ID,
            'title' => $event->post_title,
            'start' => $calendar_start,
            'end' => $calendar_end,
            'allDay' => $all_day,
            'content' => $event->post_content
        );

        array_push($calendar_events, $this_event);
    }

    $events_json = json_encode($calendar_events);

    return $events_json;
}


/**
 * nmc_event_dates function.
 *
 * @access public
 * @param int $id - ID of event
 * @return string - formatted date
 */
function nmc_event_dates($id)
{

    $startDate = strtotime(get_field('event_start_date', $id));
    $startHour = get_field('event_start_hour', $id);
    $startMin = get_field('event_start_minute', $id);
    $endDate = strtotime(get_field('event_end_date', $id));
    $endHour = get_field('event_end_hour', $id);
    $endMin = get_field('event_end_minute', $id);
    $allDay = get_field('all_day', $id);

    $date = date('M j, Y', $startDate);

    if ($allDay == 1) {

        if ($startDate != $endDate) {
            $date .=  ' - ' . date('M j, Y', $endDate);
        }

    } else {

        $start = strtotime(date('Y-m-d', $startDate) . ' ' . $startHour . ':' . $startMin);
        $end = strtotime(date('Y-m-d', $endDate) . ' ' . $endHour . ':' . $endMin);

        if ($startDate != $endDate) {
            $date = date('M j, Y', $start) . ' at ' . date('g:ia', $start) . ' - ' . date('M j, Y', $endDate) . ' at ' . date('g:ia', $end);
        } else {
            $date = date('M j, Y', $start) . ' ' . date('g:ia', $start) . ' - ' . date('g:ia', $end);
        }
    }

    return $date;
}

function nmc_event_start_date($id)
{
    $startDate = strtotime(get_field('event_start_date', $id));
    $date = date('U', $startDate);
    return $date;
}

function nmc_event_end_date($id)
{
    $endDate = strtotime(get_field('event_end_date', $id));
    $date = date('U', $endDate);
    return $date;
}