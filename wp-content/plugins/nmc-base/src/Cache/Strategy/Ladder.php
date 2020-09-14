<?php

namespace NMC_WP\Cache\Strategy;

use NMC_WP\Cache\StrategyInterface;

class Ladder extends UrlList
{
    public function setUrls(\WP_Post $post)
    {
        $full_url = \get_permalink($post);
 
        if (!$full_url) {
            return;
        }

        $full_url = parse_url($full_url, \PHP_URL_PATH);

        $urls = ['/'];
        $full_url_parts = explode('/', trim($full_url, '/'));
        while ($full_url_parts) {
            $url = '/' . implode('/', $full_url_parts);
            $urls[] = $url;
            array_pop($full_url_parts);
        }

        $this->urls = $urls;
    }
}
