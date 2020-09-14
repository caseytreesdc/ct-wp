<?php

/**
 * nmc_get_breadcrumb function.
 *
 * @access public
 * @param int $id
 * @return string - breadcrumb markup
 *
 * Requires custom fields for each post type.
 * Example fields in Options, returns post ID for parent page.
 * Custom fields must be named "[post_type]_parent", e.g. news_parent
 */


function nmc_get_breadcrumb($id) {

    // Always include Home
    $breadcrumb = '<a href="' . get_bloginfo('url') . '">Home</a>';

    if (isset($id) && $id != '') {
        $source = $id;

        // Change source for custom post type singles
        if (get_post_type() != 'page') {
            $label = get_post_type() . '_parent';
            $source = get_field($label, 'option');
        }

        // Include ancestors of selected post
        $ancestors = array_reverse(get_post_ancestors($source));
        foreach($ancestors as $item) {
            $link = get_post($item);
            $breadcrumb .= '<a href="' . get_permalink($item) . '">' . $link->post_title . '</a>';
        }

        // Applies if on a post type single, source was changed above
        if ($source != $id) {
            $sourceObj = get_post($source);
            $breadcrumb .= '<a href="' . get_permalink($sourceObj->ID) . '">' . $sourceObj->post_title . '</a>';
        }

        // Always include current page
        $current = get_post($post->ID);
        $breadcrumb .= '<a>' . $current->post_title . '</a>';

    } else {

        if (is_404()) {
            $breadcrumb .= '<a>404: Page Not Found</a>';
        }
    }

    return $breadcrumb;
}