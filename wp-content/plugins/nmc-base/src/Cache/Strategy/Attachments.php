<?php

namespace NMC_WP\Cache\Strategy;

use NMC_WP\Cache\StrategyInterface;

class Attachments extends UrlList
{
    public function setUrls(\WP_Post $post)
    {
        $urls = [];
        $attachments = get_attached_media('', $post);
        if (is_array($attachments)) {
            foreach ($attachments as $a) {
                $url = $a->guid;
                $path = parse_url($url, \PHP_URL_PATH);
                if ($path) {
                    $urls[] = $path;
                }
            }
        }
        $this->urls = $urls;
    }
}
