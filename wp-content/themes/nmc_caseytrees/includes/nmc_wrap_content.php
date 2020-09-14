<?php
add_filter('the_content','nmc_wrap_content');
function nmc_wrap_content($content) {
	if ($content) {
	    $content = '<div class="the_content">'.$content.'</div>';
    }
    return $content;
}