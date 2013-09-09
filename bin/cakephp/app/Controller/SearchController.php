<?php

/**
 * Class SearchController
 */

use Swagger\Annotations as SWG;

class SearchController extends AppController
{
    public $uses = array();


    /**
     * Search using the given query string
     *
     * @SWG\Operation(
     *      partial="search",
     *      summary="Return log list based on POST JQL search criteria",
     *      notes="Users can only search across those Clients to which they have access."
     * )
     */
    public function search()
    {
        // TODO
        $this->set('todo', 'Search');
        $this->set('_serialize', array('todo'));
    }


    /**
     * Search using the given query string and export as an XLS
     *
     * @SWG\Operation(
     *      partial="search.export",
     *      summary="Instigate download of XLS containing search results",
     *      notes="Replicates the functionality of Search, with XLS output."
     * )
     */
    public function export()
    {
        // TODO
        $this->set('todo', 'Search Export');
        $this->set('_serialize', array('todo'));
    }


    /**
     * Fetch params for the Search Query Builder Wizard
     *
     * @SWG\Operation(
     *      partial="search.wizard.params",
     *      summary="Return field parameters for Query Builder",
     *      notes="Search params are limited to those from Clients to which this User has access."
     * )
     */
    public function wizardParams()
    {
        // TODO
        $this->set('todo', 'Wizard Params');
        $this->set('_serialize', array('todo'));
    }


    /**
     * Translate between QueryBuilder SQL and JQL
     *
     * @SWG\Operation(
     *      partial="search.wizard.translate",
     *      summary="Translate between SQL and JS. Bi-directional.",
     *      notes=""
     * )
     */
    public function wizardTranslate()
    {
        // TODO
        $this->set('todo', 'Wizard Translate');
        $this->set('_serialize', array('todo'));
    }

}
