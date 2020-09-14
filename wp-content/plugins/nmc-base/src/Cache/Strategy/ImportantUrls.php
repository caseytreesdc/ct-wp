<?php

namespace NMC_WP\Cache\Strategy;

use NMC_WP\Cache\StrategyInterface;

class ImportantUrls extends UrlList
{
    public function setUrls(\WP_Post $post)
    {
        $urls = [];
        $nmc_json_path = realpath(ABSPATH . '/../nmc.json');
        if (file_exists($nmc_json_path)) {
            $nmc_json_raw = file_get_contents($nmc_json_path);
            if ($nmc_json_raw) {
                $nmc_json_data = json_decode($nmc_json_raw, true);
                if ($nmc_json_data && isset($nmc_json_data['important_urls'])) {
                    $urls = $nmc_json_data['important_urls'];
                }
            }
        }

        $this->urls = $urls;
    }
}
