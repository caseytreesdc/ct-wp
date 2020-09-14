<?php
namespace NMC_WP\Cdn;

class Adapter
{
    protected $url_maker;
    protected $asset_version;
    protected $disable_cdn;

    public function __construct(
        Url $url_maker,
        string $asset_version,
        bool $disable_cdn = false
    ) {
        $this->url_maker = $url_maker;
        $this->asset_version = $asset_version;
        $this->disable_cdn = $disable_cdn;
    }

    /**
     * imageresize()
     *
     * Twig interface for the imageresize() function -
     * takes the full url, plus an array of options. Spits
     * out the new url of the resized/manipulated image.
     *
     * @param  string $url     full, absolute url to image
     * @param  array $options
     * @return string
     */
    public function imageresize($url, $options = [])
    {
        $url = trim($url);

        if ($this->disable_cdn === true) {
            return $url;
        }

        if (!$url) {
            return $url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return $url;
        }

        $parsed = parse_url($url);

        // We only want to resize images for this site.
        // If this url doesn't match our current host, we
        // don't touch it.
        if ($parsed['host'] !== $_SERVER['HTTP_HOST']) {
            return $url;
        }

        $uri = $parsed['path'];
        $path = ABSPATH . ltrim($uri, '/');

        // Check the original path. We can't
        // Work on an image that doesn't exist.
        if (!file_exists($path)) {
            return $url;
        }

        // Build final asset URL (prefix with app slug)
        $final_url = '/' . $_ENV['MYSQL_NAME'] . '/' . ltrim($uri, '/');

        /**
         * Adapt these older parameters to new ones:
         *
         * $defaults = [
         *     'resize_only'   => false,
         *     'constrain_aspect_ratio' => false,
         *     'height'        => null,
         *     'width'         => null,
         *     'crop'          => false,
         *     'grayscale'     => false,
         *     'colorize'      => false,
         *     'quality'       => 65,
         *     'background'    => null,
         *     'radius'        => null,
         *     'strip'         => true,
         *     'blur'          => null,
         *     'contrast'      => null,
         *     'interlace'     => true,
         *     'sampling'      => true,
         *     'bw'            => false,
         * ];
         */

        // Prepare new transforms with default values
        $transforms_new = [
            'v' => $this->asset_version,
            'fit' => 'cover'
        ];

        // Copy compatible properties from user options
        $supported_properties = ['v', 'dynamic', 'aspect', 'width', 'height', 'fit', 'gravity', 'strategy', 'grayscale'];
        foreach ($supported_properties as $prop) {
            if (isset($options[$prop])) {
                $transforms_new[$prop] = $options[$prop];
            }
        }

        if (isset($options['resize_only'])) {
            // NOTE: We ignore the `constrain_aspect_ratio` legacy option. Is there
            // a scenario where we would NOT want to constrain the aspect ratio?
            // Our new resizer does not support unconstrained aspect ratios.
            $transforms_new['fit'] = 'contain';
        }

        if (isset($options['bw'])) {
            $transforms_new['grayscale'] = '1';
        }

        // Generator URL
        return $this->url_maker->create($final_url, $transforms_new);
    }
}
