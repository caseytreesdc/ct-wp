<?php

namespace NMC_WP;

/**
 * Sanitize
 *
 * Helper class for sanitizing and escaping
 * data.
 */
class Sanitize
{

    /**
     * Sanitize
     *
     * Run a Wordpress sanitization function
     * on a string.
     *
     * @return
     */
    public function wordpressSanitize($string, $method)
    {
        if (!$this->isWordpressSanitizationFunction($method)) {
            throw new \Exception($method.' is not a Wordpress sanitization method.');
        }

        return $method($string);
    }

    /**
     * Sanitize Array
     *
     * Run a Wordpress sanitization function
     * on every element of an array. Only supports
     * single-dimension arrays.
     *
     * @return
     */
    public function wordpressSanitizeArray($array, $method)
    {
        if (!$this->isWordpressSanitizationFunction($method)) {
            throw new \Exception($method.' is not a Wordpress sanitization method.');
        }

        if (!is_array($array)) {
            return false;
        }

        return array_filter($array, function ($string) use ($method) {
            return $method($string);
        });
    }

    /**
     * Wordpress Sanitize Slug Array
     *
     * @param  array $array
     * @return array
     */
    public function wordpressSanitizeSlugArray($array)
    {
        return $this->wordpressSanitizeArray($array, 'sanitize_title');
    }

    /**
     * For Number
     *
     * @param  string $string
     * @return int
     */
    public function forceNumber($string)
    {
        return intval($string);
    }

    /**
     * Is Wordpress Sanitization Function
     *
     * @param  string $method
     * @return boolean
     */
    public function isWordpressSanitizationFunction($method)
    {
        return in_array($method, self::getWordpressSanitizationFunctionWhitelist());
    }

    /**
     * Get Wordpress Sanitization Whitelist
     *
     * List of functions here:
     * https://developer.wordpress.org/themes/theme-security/data-sanitization-escaping/
     *
     * @return array
     */
    public static function getWordpressSanitizationFunctionWhitelist()
    {
        return [
            'sanitize_email',
            'sanitize_file_name',
            'sanitize_html_class',
            'sanitize_key',
            'sanitize_key',
            'sanitize_mime_type',
            'sanitize_option',
            'sanitize_sql_orderby',
            'sanitize_text_field',
            'sanitize_title',
            'sanitize_title_for_query',
            'sanitize_title_with_dashes',
            'sanitize_user',
            'esc_url_raw',
            'wp_filter_post_kses',
            'wp_filter_nohtml_kses'
        ];
    }
}
