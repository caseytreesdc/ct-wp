<?php
namespace NMC_WP\Cdn;

class Url
{
    /**
     * The URL base url (scheme://domain)
     * @var string
     */
    protected $baseUrl;

    /**
     * The HMAC-256 secret key for URL signing
     * @var [type]
     */
    protected $secretKey;

    /**
     * Constructor
     * @param string $baseUrl   The URL base url
     * @param string $secretKey The HMAC-256 secret key
     */
    public function __construct(string $baseUrl, string $secretKey)
    {
        $this->baseUrl = $baseUrl;
        $this->secretKey = $secretKey;
    }

    /**
     * Create image URL
     *
     * @param  string $originalObjectKey The original S3 object key
     * @param  array  $transforms        Array of image transformations
     * @return string
     */
    public function create(string $originalObjectKey, array $transforms) : string
    {
        // Get transforms
        $transforms = $this->validateTransforms($transforms);
        $isDynamic = isset($transforms['dynamic']);

        // Extract path parts
        $keyParts = pathinfo($originalObjectKey);
        $extension_original = $keyParts['extension'] ?? 'jpg';

        // Begin URL
        $url = '/' . trim($originalObjectKey, '/');

        // Build transform string
        $transformStringParts = [];
        foreach ($transforms as $key => $value) {
            if ($key === 'width' && $isDynamic) {
                continue;
            }
            if ($key === 'height' && $isDynamic) {
                continue;
            }
            $transformStringParts[] = sprintf('%s:%s', $key, $value);
        }
        $url .= '/' . implode('-', $transformStringParts);

        // Append basename + format
        $urlForSignature = $url . '/' . $keyParts['filename']; // We don't sign the extension
        if ($isDynamic) {
            $url .= '/' . sprintf(
                '%s--%s.%s',
                $keyParts['filename'],
                $transforms['width'],
                $extension_original
            );
        } else {
            $url .= '/' . $keyParts['filename'] . '.' . $extension_original;
        }

        // Sign URL
        $signatureBytes = hash_hmac('sha256', $urlForSignature, $this->secretKey, true);
        $signatureHex = bin2hex($signatureBytes);

        return rtrim($this->baseUrl, '/') .  $url . '?signature=' . $signatureHex;
    }

    /**
     * Validate format
     *
     * @param  string $format The desired format without leading dot ("jpg", "png", "webp")
     * @return string
     */
    public function validateFormat(string $format) : string
    {
        $validFormats = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($format, $validFormats)) {
            throw new \InvalidArgumentException('Invalid format. Must be one of: ' . implode(', ', $validFormats));
        }

        return $format;
    }

    /**
     * Validate image transforms
     *
     * @param  array  $transforms Requested transforms
     * @return array              Sanitized and sorted transforms
     */
    public function validateTransforms(array $transforms) : array
    {
        // Collect valid transforms
        $validTransforms = [
            'v',
            'dynamic',
            'aspect',
            'width',
            'height',
            'fit',
            'grayscale',
            'strategy',
            'gravity'
        ];
        $transforms = array_filter($transforms, function ($value, $key) use ($validTransforms) {
            return in_array($key, $validTransforms);
        }, ARRAY_FILTER_USE_BOTH);

        // Ensure a width is provided
        if (!isset($transforms['width'])) {
            throw new \InvalidArgumentException('Images require an integer `width` transform parameter');
        }
        $transforms['width'] = (int)$transforms['width'];

        // Verify dynamic image parameters
        $isDynamic = isset($transforms['dynamic']);
        if ($isDynamic) {
            // Make sure `dynamic` is `1`
            $transforms['dynamic'] = 1;

            // Ensure aspect is provided
            if (!isset($transforms['aspect']) || !is_numeric($transforms['aspect'])) {
                throw new \InvalidArgumentException('Dynamic images require a numeric `aspect` transform parameter');
            }

            // Remove height if specified
            unset($transforms['height']);
        }

        // Verify static image parameters
        if (!$isDynamic) {
            // Ensure a height is provided if `fit` === 'cover'
            if (isset($transforms['fit']) && $transforms['fit'] === 'cover') {
                if (!isset($transforms['height'])) {
                    throw new \InvalidArgumentException('Images require an integer `height` transform parameter when the `fit` transform parameter value is "cover"');
                }
                $transforms['height'] = (int)$transforms['height'];
            }
        }

        // Make sure `grayscale` is `1`
        if (isset($transforms['grayscale'])) {
            $transforms['grayscale'] = 1;
        }

        // Sort transforms per `$validTransforms` array above
        $finalTransforms = [];
        foreach ($validTransforms as $index => $name) {
            if (isset($transforms[$name])) {
                $finalTransforms[$name] = $transforms[$name];
            }
        }

        return $finalTransforms;
    }
}
