<?php

App::uses('AppController', 'Controller');
App::import('vendor', 'JqlParser/JqlParser');

use Swagger\Annotations as SWG;
use JqlParser\JqlParser;

/**
 * Class SearchController
 */
class SearchController extends AppController
{
    public $uses = array('Search', 'JqlParser');

    /**
     * Search using the given query string
     *
     * @SWG\Operation(
     *      partial="search",
     *      summary="Return log list based on POST JQL search criteria",
     *      notes="Users can only search across those Clients to which they have access.",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="query",
     *              paramType="query",
     *              dataType="string",
     *              required="true",
     *              description="jql to search on"
     *          )
     *      )
     * )
     */
    public function search()
    {
        // Perform search
        // Returns Logs and Options to accompany
        $return = $this->executeSearch( $this->request->query );

        // Return search result
        $this->set($return);
        $this->set('_serialize', array_keys($return));
    }


    /**
     * Search using the given query string and export as an XLS
     *
     * @SWG\Operation(
     *      partial="search.export",
     *      summary="Instigate download of XLS containing search results",
     *      notes="Replicates the functionality of Search, with XLS output.",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="query",
     *              paramType="query",
     *              dataType="string",
     *              required="true",
     *              description="jql to search on"
     *          )
     *      )
     * )
     */
    public function export()
    {
        // Perform search
        // Returns Logs and Options to accompany
        $return = $this->executeSearch( $this->request->query );

        // Generate export XLS from data
        $this->set($return);
        $this->viewClass = 'View';
        $this->render('export_xls');

        // Complete request
        exit();
    }


    /**
     * Perform the search operation and return a series of log data sufficient for search results.
     * TODO: This should really be paginated to avoid problems with large result sets
     */
    protected function executeSearch( $params )
    {
        // TODO

        $results = array();
        $options = array();

        // Validate: Search criteria must not be empty
        if ( !isset($params['query']) || empty($params['query']) )
        {
            $this->errorBadRequest(array('message'=>"Search parameters must not be empty. Please supply a valid JQL query to the 'query' variable."));
        }

        // Get the query
        $query = $params['query'];

        // Validate: If the users permissions are "single-client",
        // add a query value which ensures they only get results from their own client_id
        if (false)
        {
            $query .= 'AND client_id = my_client_id';
        }

        // Translate query to Mongo
        $query = $query;

        // Do query
        $results = array($query);

        // Skim results for client types
        foreach ($results as $k=>$result)
        {
            // Collate the list of clients for fetching the field format
            $clients[] = $result['client_id'];

            // Drop the Accountability and Status fields if we don't have permission
            if (false)
            {
                unset($results[$k]['accountability']);
                unset($results[$k]['status']);
            }
        }

        // Fetch the client field opts from the Log system
        // Return an array of options by client
        foreach ($clients as $client)
        {
            $options[$client] = $this->Client->getOptionsByClientId( $client );
        }

        // Return the Results and the corresponding Client opts
        return array( $results, $options );
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
