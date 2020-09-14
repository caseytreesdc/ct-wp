<?php
namespace NMC_WP\Cache;

use WP_Post;

class Purger
{
    /**
     * @var string The current website's hostname
     */
    protected $hostname;

    /**
     * @var string The Varnish server IP
     */
    protected $varnish_ip;

    /**
     * @var array URL-fetching strategies
     */
    protected $strategies;

    /**
     * Constructor
     *
     * @param string $hostname The current website's hostname
     * @param string $varnish_ip The Varnish server IP
     */
    public function __construct(string $hostname = null, string $varnish_ip = '127.0.0.1')
    {
        $this->hostname = ($hostname) ? $hostname : self::getDefaultHostname();
        $this->varnish_ip = $varnish_ip;
        $this->strategies = [];
    }

    /**
     * Add URL-fetching strategy
     *
     * @param StrategyInterface $strategy
     * @return Purger
     */
    public function addStrategy(StrategyInterface $strategy) : Purger
    {
        $this->strategies[] = $strategy;

        return $this;
    }

    /**
     * Run Purge Strategies for a Given Wordpress Post
     *
     * @param WP_Post The post object
     * @return array Array of URLs (keys) and whether they were purged (values)
     */
    public function purge(WP_Post $post)
    {
        foreach ($this->strategies as $strategy) {
            $strategy->runPurge($post);
        }
    }

    /**
     * Ban a URL
     */
    public static function banSite($hostname = null, string $varnish_ip = '127.0.0.1')
    {
        if (!$hostname) {
            $hostname = self::getDefaultHostname();
        }
        // Some WP sites use nightly async scripts invoked via cron and do not
        // specify all necessary env vars causing `get_site_url()` to return
        // an invalid hostname. Stop here if hostname is invalid.
        if (!$hostname) {
            return;
        }
        $ch = curl_init('http://' . $varnish_ip);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Host: ' . $hostname
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'BAN');
    
        curl_exec($ch);
    }

    /**
     * Purge a URL
     */
    public static function purgeUrl($url, $hostname = null, string $varnish_ip = '127.0.0.1')
    {
        if (!$hostname) {
            $hostname = self::getDefaultHostname();
        }
        // Some WP sites use nightly async scripts invoked via cron and do not
        // specify all necessary env vars causing `get_site_url()` to return
        // an invalid hostname. Stop here if hostname is invalid.
        if (!$hostname) {
            return;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PURGE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Host: ' . $hostname
        ]);

        curl_setopt($ch, CURLOPT_URL, 'http://' . $varnish_ip . '/' . ltrim($url, '/'));
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (!$result or $http_status !== 200) ? false : true;
    }

    /**
     * Get Default Hostname
     *
     * @return string
     */
    public static function getDefaultHostname()
    {
        return wp_parse_url(get_site_url(), PHP_URL_HOST);
    }
}
