<?php

// Checks for current page masthead. If not, checks up the tree.
// Will need to account for post type singles inheriting parent page's info

function nmc_masthead($id) {

    if (isset($id)) {
        $source = $id;
    } else {
        $source = $post->ID;
    }

    $ancestors = get_post_ancestors($source);
    $masthead = get_field('masthead', $source);

    if (!$masthead[0]['image']) {

        if (get_post_type() != 'page') {
            $label = get_post_type() . '_parent';
            $parent = get_field($label, 'option');
            $masthead = get_field('masthead', $parent);

            if (!$masthead[0]['image']) {
                $ancestors = get_post_ancestors($parent);
                for($i=0; $i<=sizeof($ancestors); $i++){
                    $masthead = get_field('masthead', $ancestors[$i]);
                    if($masthead[0]['image']){
                        break;
                    }
                }
            }

        } else {

            for($i=0; $i<=sizeof($ancestors); $i++){
                $masthead = get_field('masthead', $ancestors[$i]);
                if($masthead[0]['image']){
                    break;
                }
            }
        }
    } 

    return $masthead;
}