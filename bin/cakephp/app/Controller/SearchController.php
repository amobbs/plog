<?php

App::uses('AppController', 'Controller');

use Preslog\JqlParser\JqlOperator\JqlOperator;
use Preslog\JqlParser\JqlParser;
use Swagger\Annotations as SWG;

/**
 * Class SearchController
 */
class SearchController extends AppController
{
    public $uses = array('Search', 'JqlParser', 'Log', 'Client', 'User');

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
        $orderBy =  isset($this->request->query['order']) ? $this->request->query['order'] : '';
        $asc = isset($this->request->query['orderasc']) ? $this->request->query['orderasc'] == 'true' : true;

        // Perform search
        // Returns Logs and Options to accompany
        $return = $this->executeSearch( $this->request->query, $limit, $start, $orderBy, $asc);

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
        $orderBy =  isset($this->request->query['order']) ? $this->request->query['order'] : '';
        $asc = isset($this->request->query['orderasc']) ? $this->request->query['orderasc'] == 'true' : true;

        // Perform search
        // Returns Logs and Options to accompany
        $return = $this->executeSearch( $this->request->query, -1, 0, $orderBy, $asc);

        // Generate export XLS from data
        $this->set($return);
        $this->viewClass = 'View';
        $this->render('export_xls');

        // Complete request
        exit();
    }


    /**
     * Perform the search operation and return a series of log data sufficient for search results.
     */
    protected function executeSearch( $params, $limit = 3, $start = 0, $orderBy = '', $orderAsc = true)
    {
        $options = array();

        // Validate: Search criteria must not be empty
        if ( !isset($params['query']) || empty($params['query']) )
        {
            $this->errorBadRequest(array('message'=>"Search parameters must not be empty. Please supply a valid JQL query to the 'query' variable."));
        }

        $query = $params['query'];

        // TODO Validate: If the users permissions are "single-client",
        // add a query value which ensures they only get results from their own client_id
        if (false)
        {
            $query .= 'AND client_id = my_client_id';
        }


        $user = $this->User->findById(
            $this->PreslogAuth->user('_id')
        );


        $clients = $this->User->listAvailableClientsForUser($user['User']);
        $fullClients = array();
        foreach($clients as $client) {
            $c = $this->Client->findById($client['_id']);
            $fullClients[] = $c['Client'];
            $options[(string)$client['_id']] = $c['Client'];
        }

        // Do query
        $results = $this->Log->findByQuery($query, $fullClients, $orderBy, $start, $limit, $orderAsc);
        $total = $this->Log->countByQuery($query, $fullClients);

        $clients = array();
        $users = array();
        //loop results for client, created by users and any other info we will need to grab for display
        foreach ($results as $k=>$result)
        {
            // Collate the list of clients for fetching the field format
            $clients[] = $result['Log']['client_id'];

            // Drop the Accountability and Status fields if we don't have permission
            if (false)
            {
                unset($results[$k]['accountability']);
                unset($results[$k]['status']);
            }
        }

        //TODO Fetch the users involved
        $userObjects = array();
        if (!empty($users)) {
            $userObjects = $this->Log->listUsersByIds($users);
        }

        //list all fields that we can use to sort these logs
        $allFieldNames = array();

        //loop through the logs again and reformat them for display
        $logs = array();
        foreach ($results as $k=>$log) {
            $allFieldNames['hrid'] = true; // so we can search on log id TODO find a way to give this a better name

            $log = $log['Log'];

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
                ),
            );

            //add all the custom attributes into display
            foreach($log['fields'] as $field) {
                $clientEntity = $this->Client->getClientEntityById((string)$log['client_id']);

                //get field info from the client
                $fieldInfo = $this->_getFieldFromClientById($field['field_id'], $options[(string)$log['client_id']]);

                $formattedField = array(
                    'title' => $fieldInfo['name'],
                    'value' => '',
                    'showTooltip' => false
                );
                $includeField = true;

                $fieldName = $fieldInfo['name'];
                //different fields get displayed in different ways
                switch ($fieldInfo['type']) {
                    case 'loginfo':
                        $formattedField = array(
                            'title' => 'Created',
                            'value' => $field['data']['created'],
                            'showTooltip' => false
                        );
                        $fieldName = 'Created';
                        $formattedField = array(
                            'title' => 'Modified',
                            'value' => $field['data']['modified'],
                            'showTooltip' => false
                        );
                        $fieldName = 'Modified';

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
                    $allFieldNames[$fieldName] = true;
                }
            }

            $logs[] = $parsed;
        }

        // Return the Results and the corresponding Client opts
        return array('query' => $query, 'logs' => $logs,  'fields' => array_keys($allFieldNames),'total' => $total);
    }

    //TODO remove this should be on the field type
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

        //get list of fields that can be used to query against (red query builder format)
        $meta = $this->_getQueryBuilderMeta();


        $this->set('sql', $parser->getSql());
        $this->set('args', $parser->getArguments());
        $this->set('fieldList', $meta['fieldList']);
        $this->set('selectOptions', $meta['selectOptions']);
        $this->set('_serialize', array('sql', 'args', 'fieldList', 'selectOptions'));
    }

    private function _getQueryBuilderMeta()
    {
        //list clients this user has access to
        $clientIds = $this->getClientListForUser();


        //get all the unique fields that can be used from all the clients this user has access to
        $columns = array(
            'ID' => array(
                'name' => 'ID',
                'label' => 'ID',
                'type' => 'TEXT',
                'size' => 15,
            ),
            'created' => array(
                'name' => 'CREATED',
                'label' => 'Created',
                'type' => 'DATE',
                'size' => 50,
            ),
            'modified' => array(
                'name' => 'MODIFIED',
                'label' => 'Modified',
                'type' => 'DATE',
                'size' => 50,
            ),
            'version' => array(
                'name' => 'VERSION',
                'label' => 'Version',
                'type' => 'TEXT',
                'size' => 4,
            ),
            'text' => array(
                'name' => 'TEXT',
                'label' => 'Text',
                'type' => 'TEXT',
                'size' => 150,
            ),
        );

        $types = array(); //defines how a value is displayed in the interface, textbox or drop down etc..
        $types['DATE'] = array(
            'editor' => 'DATE',
            'name' => 'DATE',
            'operators' => $this->listOperators('DATE'),
        );
        $types['DURATION'] = array(
            'editor' => 'TEXT',
            'name' => 'DURATION',
            'operators' => $this->listOperators('DURATION'),
        );
        $types['TEXT'] = array(
            'editor' => 'TEXT',
            'name' => 'TEXT',
            'operators' => $this->listOperators('TEXT'),
        );


        $selectOptions = array(); //list of options that are available for SELECt types
        foreach($clientIds as $id)
        {
            $fieldTypeName= '';
            $clientEntity = $this ->Client->getClientEntityById($id);
            foreach($clientEntity->fields as $fieldId => $clientField)
            {
                $fieldSettings = $clientField->getFieldSettings();
                $title = $fieldSettings['label'];
                if (isset($columns[$title]) || $fieldSettings['type'] == 'loginfo')
                {
                    //created/modified etc.. are manually added above. so there is nothing else to do for this
                    continue;
                }

                $fieldType = $clientField->getProperties('queryFieldType');
                $fieldTypeName = $fieldType;

                //each select field needs their own type because they have different options
                if ($clientField instanceof \Preslog\Logs\FieldTypes\Select)
                {
                    $fieldTypeName = $title;
                    $upperTitle = strtoupper($title);
                    $types[$upperTitle] = array(
                        'editor' => 'SELECT',
                        'name' => $upperTitle,
                        'operators' => $this->listOperators('SELECT'),
                    );

                    $options = array();
                    foreach($fieldSettings['data']['options'] as $option)
                    {
                        $options[] = array(
                            'value' => $option['_id'],
                            'label' => $option['name']
                        );
                    }
                    $selectOptions[$upperTitle] = $options;
                }

                $columns[strtoupper($title)] = array(
                    'name' => strtoupper($title),
                    'label' => $title,
                    'type' => strtoupper($fieldTypeName),
                    'size' => 100,
                );


            }

            //add attributes to the list
            $clientEntityArray = $clientEntity->toArray();
            foreach($clientEntityArray['attributes'] as $attribute)
            {
                $name = $attribute['name'];
                if ( ! isset($columns[$name]) )
                {
                    $columns[$name] = array(
                        'name' => $name,
                        'label' => $name,
                        'type' => $name,
                        'size' => 100,
                    );

                    $types[$name] = array(
                        'editor' => 'SELECT',
                        'name' => $name,
                        'operators' => $this->listOperators('SELECT'),
                    );
                }

                $options = array();
                foreach($attribute['children'] as $child)
                {
                    if ( ! $child['deleted'])
                    {
                        $options[] = array(
                            'value' => $child['name'],
                            'label' => $child['name']
                        );

                        foreach($child['children'] as $subChild)
                        {
                            if ( ! $subChild['deleted'])
                            {
                                $options[] = array(
                                    'value' => $subChild['name'],
                                    'label' => $subChild['name']
                                );
                            }
                        }
                    }
                }
                if ( isset($selectOptions[$name]) )
                {
                    $selectOptions[$name] = array_merge($selectOptions[$name], $options);
                }
                else
                {
                    $selectOptions[$name] = $options;
                }
            }
        }

        $fieldList = array(
            'tables' => array(
                array(
                    'name' => 'LOGS',
                    'columns' => array_values($columns),
                    'fks' => array(),
                ),
            ),
            'types' => array_values($types),
        );

        return array('fieldList' => $fieldList, 'selectOptions' => $selectOptions);
    }

    /**
     * given a string find if it matches a log id or if it is just plain text and return some jql that will find it.
     *
     * @SWG\Operation(
     *      partial="search.wizard.quick",
     *      summary="return jql from quick search string",
     *      notes="",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="search_text",
     *              paramType="query",
     *              dataType="string",
     *              required="true",
     *              description="search text that will be converted to jql"
     *          )
     *      )
     * )
     */
    public function convertQuickSearchToJql()
    {
        $searchText = $this->request->query['search_text'];
        $jql = '';

        $config = Configure::read('Preslog');
        $logRegex = $config['regex']['logid'];

        //split the log prefix from numeric log id
        $parts = array();
        if ( preg_match($logRegex, $searchText, $parts) )
        {
            $jql = 'ID = ' . $searchText;
        }
        else
        {
            $jql = 'text ~ "' . $searchText . '"';
        }

        $this->set('jql', strtoupper($jql));
        $this->set('_serialize', array('jql'));
    }

    private function listOperators($type)
    {
        $operators = array();
        foreach(JqlOperator::listOperators() as $label => $operator)
        {
            if ($operator->isAppliedTo($type))
            {
                $operators[] = array(
                    'name' => $operator->getJqlSymbol(),
                    'label' => $operator->getHumanReadable(),
                    'cardinality' => 'ONE',
                );
            }
        }

        return $operators;
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



    //TODO this code is duplicated in dashboard controoller, put it in a common place
    /**
     * get the list of clients the logged in user can access
     * @return mixed
     */
    private function getClientListForUser() {
        $user = $this->User->findById(
            $this->PreslogAuth->user('_id')
        );

        $clients = $this->User->listAvailableClientsForUser($user['User']);

        $clientIds = array();
        foreach($clients as $client) {
            $clientIds[] = $client['_id'];
        }
        return $clientIds;
    }

}
