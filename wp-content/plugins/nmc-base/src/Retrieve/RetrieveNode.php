<?php
namespace NMC_WP\Retrieve;

use \Twig\Compiler as Compiler;
use \Twig\Node\Node as Node;
use \Twig\Node\Expression\AbstractExpression as AbstractExpression;

class RetrieveNode extends Node
{
    public function __construct($name, $url, $format, $cache, $line, $tag = null)
    {
        parent::__construct([], ['name' => $name, 'url' => $url, 'format' => $format, 'cache' => $cache], $line, $tag);
    }

    /**
     * Compile
     *
     * @param  Compiler $compiler
     * @return void
     */
    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$context[\''.$this->getAttribute('name').'\'] = \NMC_WP\Retrieve\RetrieveNode::get(')
            ->subcompile($this->getAttribute('url'))
            ->write(", '".$this->getAttribute('format')."'");

        if ($this->getAttribute('cache')) {
            $compiler
                ->raw(", '")
                ->subcompile($this->getAttribute('cache'))
                ->raw("'");
        }

        $compiler->raw(");\n");
    }

    /**
     * Get
     *
     * Get a URL and return the data in the
     * specified format.
     *
     * @param  string  $url
     * @param  string  $format
     * @param  integer $cache
     * @return [type]          [description]
     */
    public static function get($url, $format, $cache = 1)
    {
        $key = 'rtag-'.md5($url);
        $cache = max([$cache, 15]); // minimum cache time of 15 minutes
        $content = get_transient($key);

        // if cached and cached data found
        // return formatted output
        if ($content !== false) {
            //delete_transient($key); //<- testing purposes
            return $content;
        } else {
            $rawContent = [];

            try {
                $rawContent = @file_get_contents(filter_var($url, FILTER_SANITIZE_URL));
                if (!$rawContent || !strlen($rawContent)) {
                    throw new \Exception();
                }
            } catch (\Exception $e) {
                // Return an empty iterable
                // still cache to avoid repeating long requests on an unresponsive url
                set_transient($key, $content, MINUTE_IN_SECONDS * $cache);
                return [];
            }

            if (strlen($rawContent)) {
                $content = self::parseContent($rawContent, $format);
            }

            set_transient($key, $content, MINUTE_IN_SECONDS * $cache);
        }

        return $content;
    }

    /**
     * Parse Content
     *
     * @param  string $raw
     * @param  string $format
     * @return mixed
     */
    protected static function parseContent($raw = '', $format = 'text')
    {
        switch ($format) {
            case 'json':
                return json_decode($raw, true);
                break;
            case 'csv':
                $content = [];
                $rows = str_getcsv($raw, "\n");
                $h = str_getcsv($rows[0]);
                array_unshift($rows);
                foreach ($rows as $row) {
                    $content[]=array_combine($h, str_getcsv($row));
                }
                return $content;
                break;
            case 'rss':
                $content = [];
                $rss = new \DomDocument();
                $rss->loadXML($raw);
                foreach ($rss->getElementsByTagName('item') as $node) {
                    $item = [];
                    foreach ($node->childNodes as $child) {
                        $item[$child->nodeName] = $child->nodeValue;
                    }
                    $content[] = $item;
                }
                return $content;
                break;
            default:
                return $raw;
                break;
        }
    }
}
