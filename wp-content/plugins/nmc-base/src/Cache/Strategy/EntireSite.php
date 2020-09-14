<?php

namespace NMC_WP\Cache\Strategy;

use NMC_WP\Cache\Purger;
use NMC_WP\Cache\StrategyInterface;

/**
 * Entire Site Purge Strategy
 */
class EntireSite implements StrategyInterface
{
    public function runPurge(\WP_Post $post)
    {
        Purger::banSite();
    }
}
