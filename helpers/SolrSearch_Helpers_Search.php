<?php

class SolrSearch_Helpers_Search
{


    /**
     * Construct the Solr search parameters.
     *
     * @return array Array of fields to pass to Solr
     */
    static function getParameters($facets)
    {

        return array(

            'facet'               => 'true',
            'facet.field'         => $facets,
            'facet.mincount'      => 1,
            'fq'                  => SolrSearch_Helpers_Facet::parseFilters(),
            'facet.limit'         => get_option('solr_search_facet_limit'),
            'facet.sort'          => get_option('solr_search_facet_sort'),
            'hl'                  => get_option('solr_search_hl')?'true':'false',
            'hl.snippets'         => get_option('solr_search_hl_snippets'),
            'hl.fragsize'         => get_option('solr_search_hl_fragsize'),
            'hl.maxAnalyzedChars' => get_option('solr_search_hl_max_analyzed_chars'),
            'hl.fl'               => '*_t'

        );

    }

    /**
     * Form the complete Solr query.
     *
     * @return string The Solr query.
     */
    static function getQuery($query, $facet, $limitToPublicItems = true)
    {

        // If defined, replace `:`; otherwise, revert to `*:*`.
        // Also, clean it up some.
        if (!empty($query)) {
            $query = str_replace(':', ' ', $query);
            $to_remove = array('[', ']');
            foreach ($to_remove as $c) {
                $query = str_replace($c, '', $query);
            }
        } else {
            $query = '*:*';
        }

        // Form the composite Solr query.
        if (!empty($facet)) $query .= " AND {$facet}";

        // Limit the query to public items if required
        if($limitToPublicItems) {
            $query .= ' AND public:"true"';
        }

        return $query;

    }

    /**
     * Pass setting to Solr search
     *
     * @param string $q The query string
     * @param string $facet The facet string
     * @param array $activeFacets A set of active facets
     * @param int $offset Results offset
     * @param int $limit Limit per page
     * @param bool $limitToPublicItems
     * @return Apache_Solr_Response results
     * @throws Apache_Solr_HttpTransportException
     * @throws Apache_Solr_InvalidArgumentException
     */
    static function search($q, $facet, $activeFacets, $offset, $limit, $limitToPublicItems = true)
    {
        // Get the parameters.
        $params = SolrSearch_Helpers_Search::getParameters($activeFacets);

        // Construct the query.
        $query = SolrSearch_Helpers_Search::getQuery($q, $facet, $limitToPublicItems);

        // Connect to Solr.
        $solr = SolrSearch_Helpers_Index::connect();

        // Execute the query.
        return $solr->search($query, $offset, $limit, $params);

    }
}
