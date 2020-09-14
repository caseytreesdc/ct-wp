<?php

namespace NMC_WP\Cache\Strategy;

use NMC_WP\Cache\Purger;
use NMC_WP\Cache\StrategyInterface;

/**
 * URL List Purge Strategy
 */
class UrlList implements StrategyInterface
{
    protected $urls;

    public function __construct(array $urls = [])
    {
        $this->urls = $urls;
    }

    public function runPurge(\WP_Post $post)
    {
        $this->setUrls($post);

        if (count($this->urls) == 0) {
            return null;
        }

        foreach ($this->urls as $url) {
            Purger::purgeUrl($url);
        }
    }
}
