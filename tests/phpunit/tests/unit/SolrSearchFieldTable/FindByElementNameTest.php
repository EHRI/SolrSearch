<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTableTest_FindByElementName
    extends SolrSearch_Case_Default
{


    /**
     * Delete any facet mappings registered when the plugin is installed.
     */
    public function setUp()
    {
        parent::setUp();
        $this->_clearFacetMappings();
    }


    /**
     * `findByElementName` should return the facet linked to a given element.
     */
    public function testFindByElement()
    {

        $title = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Title'
        );

        $field = new SolrSearchField($title);
        $field->save();

        $retrieved = $this->fieldTable->findByElementName(
            'Dublin Core', 'Title'
        );

        $this->assertEquals($field->id, $retrieved->id);

    }


}
