<?php

/**
 * nmc_page_title function.
 *
 * @access public
 * @param int $id
 * @return string - page title
 *
 * Requires custom fields for each post type.
 * Example fields in Options, returns post ID for parent page.
 * Custom fields must be named "[post_type]_parent", e.g. news_parent
 */

function nmc_page_title($id) {

    global $wp_query;
    
    $title = null;

    if (is_404()) {
        $title = '404: Page Not Found';
    } elseif (is_search()) {
        $title = 'Search';
    } elseif (get_post_type() == 'page') {
        
        if (isset($id) && $id != '') {
            $source = $id;
        } else {
            return false;
        }
        
    } else {
        // Change source for custom post type singles
        if (get_post_type() != 'page') {
            $label = get_post_type() . '_parent';
            $source = get_field($label, 'option');
        }       
    }

    $sourceObj = get_post($source);
    if (!$title) {
        $title = $sourceObj->post_title; 
    }

    return $title;
}