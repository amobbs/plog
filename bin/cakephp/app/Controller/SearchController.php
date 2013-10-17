<?php

App::uses('AppController', 'Controller');

use Preslog\JqlParser\JqlParser;
use Swagger\Annotations as SWG;

/**
 * Class SearchController
 */
class SearchController extends AppController
{
    public $uses = array('Search', 'JqlParser', 'Log', 'Client');

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
        $limit = isset($this->request->query['limit']) ? $this->request->query['limit'] : 3;
        $start =  isset($this->request->query['start']) ? $this->request->query['start'] : 1;
        $orderBy =  isset($this->request->query['orderBy']) ? $this->request->query['orderBy'] : '';

        // Perform search
        // Returns Logs and Options to accompany
        $return = $this->executeSearch( $this->request->query, $limit, $start, $orderBy );

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
    protected function executeSearch( $params, $limit = 3, $start = 1, $orderBy = '')
    {
        // TODO
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

        // Do query
        $results = $this->Log->findByQuery($query, $start, $limit, $orderBy);
        $total = $this->Log->countByQuery($query);

        $clients = array();
        $users = array();
        //loop results for client, created by users and any other info we will need to grab for display
        foreach ($results as $k=>$result)
        {
            // Collate the list of clients for fetching the field format
            $clients[] = $result['Log']['client_id'];
            if ($result['Log']['created_user_id'] instanceof MongoId) {
                $users[] = $result['Log']['created_user_id'];
            }
            if ($result['Log']['modified_user_id'] instanceof MongoId) {
                $users[] = $result['Log']['modified_user_id'];
            }

            // Drop the Accountability and Status fields if we don't have permission
            if (false)
            {
                unset($results[$k]['accountability']);
                unset($results[$k]['status']);
            }
        }

        //Fetch the client field opts from the Log system
        //Return an array of options by client
        foreach ($clients as $client)
        {
            $clientObject = $this->Client->findById( $client );
            $options[(string)$client] = $clientObject['Client'];
        }

        //Fetch the users involved
        $userObjects = array();
        if (!empty($users)) {
            $userObjects = $this->Log->listUsersByIds($users);
        }
        //loop through the logs again and reformat them for display
        $logs = array();
        foreach ($results as $k=>$result) {
            $log = $result['Log'];

            //create the format and add in some fields that will always be there
            $parsed = array(
                'id' => $log['_id'],
                'deleted' => $log['deleted'],
                'hrid' => $log['hrid'],
                'attributes' => array(
                    array(
                        'title' => 'LogID',
                        'value' => $log['hrid'],
                        'showTooltip' => false,
                    ),
                    array(
                        'title' => 'Created',
                        'value' => $log['created'],
                        'showTooltip' => false,
                    ),
                    array(
                        'title' => 'Modified',
                        'value' => $log['modified'],
                        'showTooltip' => false,
                    ),
                    array(
                        'title' => 'Version',
                        'value' => $log['version'],
                        'showTooltip' => false,
                    ),
                    array(
                        'title' => 'Created By',
                        'value' => $this->_getUserName($log['created_user_id'], $userObjects),
                        'showTooltip' => false,
                    ),
                    array(
                        'title' => 'Modified By',
                        'value' => $this->_getUserName($log['modified_user_id'], $userObjects),
                        'showTooltip' => false,
                    ),
//                    array(
//                        'title' => 'Company',
//                        'value' => $this->_getCompanyName($log['client_id'], $options[$log['client_id']]),
//                        'showTooltip' => false,
//                    ),
                ),
            );

            //add all the custom attributes into display
            foreach($log['fields'] as $field) {
                //get field info from the client
                $fieldInfo = $this->_getFieldFromClientById($field['field_id'], $options[(string)$log['client_id']]);

                $formattedField = array(
                    'title' => $fieldInfo['name'],
                    'value' => '',
                    'showTooltip' => false
                );
                $includeField = true;

                //different fields get displayed in different ways
                switch ($fieldInfo['type']) {
                    case 'loginfo':
                        $includeField = false;
                        break;
                    case 'datetime':
                        if ($field['data']['datetime'] instanceof MongoDate) {
                            $field['data']['datetime'] = date('Y-m-d H:i:s', $field['data']['datetime']->sec);
                        }
                        $formattedField['value'] = $field['data']['datetime'];
                        break;
                    case 'duration':
                        $formattedField['value'] = $this->_formatDuration($field['data']['seconds']);
                        break;
                    case 'select':
                        $formattedField['value'] = $this->_getSelectValueFromClient($field['data']['selected'], $fieldInfo['data']['options']);
                        break;
                    case 'textarea': //missing break on purpose, same format as default.
                        $formattedField['showTooltip'] = true;
                    default: //text, textarea etc..
                        $formattedField['value'] = $field['data']['text'];
                }

                if ($includeField) {
                    $parsed['attributes'][] = $formattedField;
                }
            }

            $logs[] = $parsed;
        }
//
//        //TODO remove this it is just here for testing.
//        if (!empty($query)) {
//            $mongo = $this->Log->getDataSource();
//            $mongo->toString($query);
//        }
        // Return the Results and the corresponding Client opts
        return array('query' => $query, 'logs' => $logs, 'fields' => $options, 'total' => $total);
    }

    private function _formatDuration($duration) {
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration - ($hours *3600) - ($minutes * 60);

        $string = $hours > 0 ? $hours . 'h ': '';
        $string .= $minutes > 0 ? $minutes . 'm ': '';
        $string .= $seconds > 0 ? $seconds . 's ': '';

        //not likely but if the string is empty show somehting
        if (empty($string)) {
            $string = '0S';
        }

        return $string;
    }

    //find the users name from the id
    private function _getUserName($id, $users) {
        foreach($users as $user) {
            if ($user['User']['_id'] == $id) {
                return $user['User']['firstName'] . ' ' . $user['User']['lastName'];
            }
        }

        return '';
    }

    //find the clients company name from its id
    private function _getCompanyName($clientId, $clients) {
        foreach($clients as $client) {
            if ($client['_id'] == (string)$clientId) {
                return $client['Client']['name'];
            }
        }

        return '';
    }

    //find field from a client given an id
    private function _getFieldFromClientById($fieldId, $clientFields) {
        foreach($clientFields['fields'] as $field) {
            if ($field['_id'] == $fieldId) {
                return $field;
            }
        }

        return false;
    }

    //find the text value for a select option from a client
    private function _getSelectValueFromClient($optionId, $options) {
        foreach($options as $option) {
            if ($option['_id'] == $optionId) {
                return $option['name'];
            }
        }

        return $optionId;
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
