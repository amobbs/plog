<?php

/**
 * Class SearchController
 */

App::uses('AppController', 'Controller');
App::import('vendor', 'JqlParser/JqlParser');

use Swagger\Annotations as SWG;
use JqlParser\JqlParser;

class SearchController extends AppController
{
    public $uses = array('Search', 'JqlParser');

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
     *      notes="Search params are limited to those from Clients to which this User has access.",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="jql",
     *              paramType="query",
     *              dataType="string",
     *              required="true",
     *              description="jql that will be returned as sql to be displayed by red query builder"
     *          ),
     *          @SWG\Parameter(
     *              name="args",
     *              paramType="query",
     *              dataType="string",
     *              required="true",
     *              description="arguments to be populated into resulting sql"
     *          )
     *      )
     * )
     */
    public function wizardParams()
    {
        $jql = strtoupper($this->request->query['jql']);

        $parser = new JqlParser();
        $parser->setSqlFromJql($jql);

        $this->set('sql', $parser->getSql());
        $this->set('args', $parser->getArguments());
        $this->set('_serialize', array('sql', 'args'));
    }


    /**
     * Translate between QueryBuilder SQL and JQL
     *
     * @SWG\Operation(
     *      partial="search.wizard.translate",
     *      summary="Translate between SQL and JS. Bi-directional.",
     *      notes="",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="sql",
     *              paramType="query",
     *              dataType="string",
     *              required="true",
     *              description="sql from redquery builder will be out put as jql"
     *          ),
     *          @SWG\Parameter(
     *              name="args",
     *              paramType="query",
     *              dataType="string",
     *              required="true",
     *              description="arguments to be populated into resulting jql"
     *          )
     *      )
     * )
     */
    public function wizardTranslate()
    {
        $sql = strtoupper($this->request->query['sql']);
        $args = json_decode($this->request->query['args']);

        if ($args === null) {
            throw new Exception('invalid array of args');
        }

        $parser = new JqlParser();
        $parser->setJqlFromSql($sql, $args);

        $this->set('jql', $parser->getJql());
        $this->set('args', $parser->getArguments());
        $this->set('_serialize', array('jql', 'args'));
    }

}
