<?php

namespace NMC_WP;

/**
 * Term URL Paramater
 *
 * Gets terms from a URL
 * query paramater.
 */
class TermUrlParameter
{
    /**
     * Param
     *
     * @var string
     */
    private $param = null;

    /**
     * Delimiter
     *
     * @var string
     */
    private $delimiter = ',';

    /**
     * __construct
     *
     * Take an array containing some
     * configuration, and turn it into an array
     * of terms that can be used in a query.
     */
    public function __construct($config)
    {
        // We just need a parameter.
        if (!isset($config['param'])) {
            return [];
        }

        $this->param = $config['param'];

        $validConfigs = ['param', 'delimiter'];

        foreach ($validConfigs as $configKey) {
            if (isset($config[$configKey])) {
                $this->{$configKey} = $config[$configKey];
            }
        }
    
        return $this->parseParameter();
    }

    /**
     * Parse Parameter
     *
     * Parse the value out of the URL, and
     * return an array of terms.
     *
     * @return array
     */
    public function parseParameter()
    {
        $value = $_GET[$this->param] ?? null;

        // Make everything an array, even if singular - use the delim to split url val
        $value = is_array($value) ? $value : explode($this->delimiter, $value);

        return $value;
    }
}
