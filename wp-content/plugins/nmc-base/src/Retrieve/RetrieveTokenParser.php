<?php
namespace NMC_WP\Retrieve;

use \Twig\TokenParser\AbstractTokenParser as AbstractTokenParser;
use \Twig\Token as Token;
use \NMC_WP\Retrieve\RetrieveNode as RetrieveNode;

/*
 * Token Parser for the Retrieve Tag
 */
class RetrieveTokenParser extends \Twig\TokenParser\AbstractTokenParser
{
    /**
     * Parse
     *
     * @param  Twig\Token  $token
     * @return NMC_WP\Retrieve\RetrieveNode
     */
    public function parse(Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        // Begin parsing tag
        $name = $stream->expect(Token::NAME_TYPE)->getValue();
        $stream->expect(Token::NAME_TYPE, 'from');
        $url = $this->parser->getExpressionParser()->parseExpression();

        // Parse format variable (default 'text')
        $format = 'text';
        if ($stream->test(Token::NAME_TYPE, 'as')) {
            $stream->expect(Token::NAME_TYPE, 'as');

            if ($stream->test(Token::NAME_TYPE, 'json')) {
                $format = 'json';
            } elseif ($stream->test(Token::NAME_TYPE, 'csv')) {
                $format = 'csv';
            } elseif ($stream->test(Token::NAME_TYPE, 'rss')) {
                $format = 'rss';
            }
            $this->parser->getStream()->next();
        }

        // Handle cache option
        $cache = null;
        if ($stream->test(Token::NAME_TYPE, 'cache')) {
            $stream->expect(Token::NAME_TYPE, 'cache');
            $cache = $this->parser->getExpressionParser()->parseExpression();
        }

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        return new RetrieveNode($name, $url, $format, $cache, $token->getLine(), $this->getTag());
    }

    /**
     * Get Tag
     *
     * @return string
     */
    public function getTag()
    {
        return 'retrieve';
    }
}
