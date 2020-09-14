<?php

// [pullquote quote="" name="" position=""]
function pullquote_func( $atts ) {

    $a = shortcode_atts( array(
        'quote' => '', // string, include quotes if quotes should appear
        'name' => '', // include a source if needed
        'position' => ''
    ), $atts );

    $quote = '<blockquote>';

    if ($a['quote'] != '') {
        $quote .= '<p>' . strip_tags($a['quote']) . '</p>';
    }

    if ($a['name'] != '' || $a['position'] != '') {
        $quote .= '<footer>';

        if ($a['name']) {
            $quote .= '<span class="quote-name">' . strip_tags($a['name']) . '</span>';
        }

        if ($a['position']) {
            $quote .= '<span class="quote-position">' . strip_tags($a['position']) . '</span>';
        }

        $quote .= '</footer>';
    }

    $quote .= '</blockquote>';

    return $quote;
}

add_shortcode( 'pullquote', 'pullquote_func' );
