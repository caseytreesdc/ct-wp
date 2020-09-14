<?php

namespace NMC_WP;

/**
 * Post Query
 *
 * Extension of the \Timber\PostQuery that adds
 * NMC-specific logic for query cleaning, pagination,
 * and a few other things.
 */
class PostQuery extends \Timber\PostQuery
{
    /**
     * Original Query
     *
     * Query passed to us, raw and
     * unfiltered.
     *
     * @var  array
     */
    public $originalQuery;

    /**
     * __construct
     *
     * Add some NMC-specific logic before
     * calling the Timber query __construct().
     *
     * @param mixed   	$query
     * @param string 	$post_class
     */
    public function __construct($query)
    {
        $this->originalQuery = $query;

        $query['paged'] = $this->getPage();

        $query = $this->cleanQuery($query);
        $query = $this->processTaxQuery($query);

        parent::__construct($query);
    }

    /**
     * Clean Query
     *
     * Global functionality applied to the
     * query array.
     *
     * @param  array $query
     * @return array
     */
    protected function cleanQuery($query)
    {
        // Change the default has_password functionality
        if (!isset($query['has_password'])) {
            $query['has_password'] = false;
        }

        return $query;
    }

    /**
     * Dynamic Taxonomy
     *
     * @param  array $query
     * @return array
     */
    protected function processTaxQuery($query)
    {
        if (!isset($query['tax_query'])) {
            return $query;
        }

        $new_tax_query = [];

        foreach ($query['tax_query'] as $tq) {
            $ntq = $tq;

            // Account for the option of AND / OR and dump that back to the new array
            if (is_array($tq) and is_array($tq['terms']) and isset($tq['terms']['param'])) {
                $termParameter = new TermUrlParameter($tq['terms']);
                $ntq['terms'] = $termParameter->parseParameter();
            }

            $new_tax_query[] = $ntq;
        }

        $query['tax_query'] = $new_tax_query;

        return $query;
    }

    /**
     * Get Page
     *
     * Get the current page based
     * on the URL.
     */
    public function getPage()
    {
        $pageParameter = $this->getPageParameter();

        if ($pageParameter) {
            $pageParameterVal = (isset($_GET[$pageParameter])) ? $_GET[$pageParameter] : false;
            $page = intval($pageParameterVal);

            if ($page <= 0) {
                $page = 1;
            }

            return $page;
        }

        return 1;
    }

    /**
     * Get Page Parameter
     *
     * Get the parameter that we are pulling the
     * current page number from in the URL.
     *
     * @return strng
     */
    public function getPageParameter()
    {
        $query = $this->originalQuery;

        // First, the paged param can be an array, and have a 'param'
        // key, which is the value we'll use from the URL.
        $pageParameter = (isset($query['paged']) and is_array($query['paged'])) ? $query['paged']['param'] : false;

        if ($pageParameter !== false) {
            return $pageParameter;
        }

        return apply_filters('nmc_pagination_url_parameter', 'pg');
    }

    /**
     * Set pagination with our preferences.
     *
     * @param   array $prefs
     * @return  Timber\Pagination object
     */
    public function setPagination($prefs = array())
    {
        $pagination = $this->queryIterator->get_pagination($prefs, $this->get_query());

        // The Timber pagination process seems to use
        // esc_url instead of esc_url_raw for links, so it messes up the
        // ampersands and breaks the links. This is a hack to
        // fix that.
        foreach ($pagination->pages as $key => $page) {
            if (isset($pagination->pages[$key]['link']) and $pagination->pages[$key]['link']) {
                $pagination->pages[$key]['link'] = str_replace('&#038;', '&', $pagination->pages[$key]['link']);
            }
        }

        $this->pagination = $pagination;
    }
}
