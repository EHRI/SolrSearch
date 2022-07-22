<?php

require_once BASE_DIR.'/application/controllers/api/ApiController.php';


class SolrSearch_ResultsController extends ApiController
{

    /**
     * Cache the facets table.
     */
    public function init()
    {
        $this->_fields = $this->_helper->db->getTable('SolrSearchField');
    }

    public function indexAction()
    {
        // Get pagination settings.
        $request = $this->getRequest();
        $recordType = $request->getParam('api_record_type');
        $resource = $request->getParam('api_resource');


        $perPageMax = (int) get_option('api_per_page');
        $perPageUser = (int) $request->getQuery('per_page');
        $limit = ($perPageUser < $perPageMax && $perPageUser > 0) ? $perPageUser : $perPageMax;
        $page  = $request->getQuery('page', 1);
        $start = ($page-1) * $limit;

        // determine whether to display private items or not
        // items will only be displayed if:
        // solr_search_display_private_items has been enabled in the Solr Search admin panel
        // user is logged in
        // user_role has sufficient permissions
        $user = current_user();
        if(get_option('solr_search_display_private_items')
            && $user
            && is_allowed('Items','showNotPublic')) {
            // limit to public items
            $limitToPublicItems = false;
        } else {
            $limitToPublicItems = true;
        }

        $q = $request->q;

        $facet = $request->facet;

        // Get a list of active facets.
        $activeFacets = $this->_fields->getActiveFacetKeys();

        // Execute the query.
        $results = SolrSearch_Helpers_Search::search($q, $facet, $activeFacets, $start, $limit, $limitToPublicItems);

        // Set the non-standard Omeka-Total-Results header.
        $this->getResponse()->setHeader('Omeka-Total-Results', $results->response->numFound);

        $recordsTable = $this->_helper->db->getTable($recordType);

        $recordAdapter = new Api_Item;

        // Build the data array.
        $records = array();
        foreach ($results->response->docs as $doc) {
            $record = $recordsTable->find($doc->modelid);
            $records[] = $this->_getRepresentation($recordAdapter, $record, $resource);
        }

        // Set the Link header for pagination.
        $this->_setLinkHeader($limit, $page, $results->response->numFound, $resource);

        // Urg, I hate this: return a different response type if we need to
        // send back facet info... very messy, but to do otherwise would break
        // consistency with the existing resources.
        if ($request->getQuery("facet", false)) {
            $facets = array();
            foreach ($results->facet_counts->facet_fields as $name => $counts) {
                $label = SolrSearch_Helpers_Facet::keyToLabel($name);
                $facets[$label] = array();
                foreach ($counts as $value => $count) {
                    $facets[$label][$value] = $count;
                }
            }

            $this->_helper->jsonApi(array(
                "records" => $records,
                "facets" => $facets,
                "total" => $results->response->numFound
            ));
        } else {
            $this->_helper->jsonApi($records);
        }
    }
}
