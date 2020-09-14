<?php
namespace NMC_WP\Cache;

interface StrategyInterface
{
    /**
     * Run Purge Logic for Wordpress Post
     *
     * @param WP_Post $post
     * @return string[]
     */
    public function runPurge(\WP_Post $post);
}
