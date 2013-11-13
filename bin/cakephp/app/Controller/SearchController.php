<?php

App::uses('AppController', 'Controller');

use Preslog\JqlParser\JqlOperator\JqlOperator;
use Preslog\JqlParser\JqlParser;
use Preslog\PreslogParser\PreslogParser;
use Swagger\Annotations as SWG;
use Preslog\Logs\Entities\LogEntity;

/**
 * Class SearchController
 * @property    Log     Log
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

        $variables = array();

        if (isset($this->request->query['variableStart']) && isset($this->request->query['variableEnd']))
        {
            $variables['start'] =  $this->request->query['variableStart'];
            $variables['end'] = $this->request->query['variableEnd'];
        }

        // Perform search
        // Returns Logs and Options to accompany
        $return = $this->executeSearch( $this->request->query, $limit, $start, $orderBy, $asc, $variables);

        //used for dashboards to determin which widget we are updating.
        if ( isset($this->request->query['widgetid']) )
        {
            $return['widgetid'] = $this->request->query['widgetid'];
        }

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
        set_time_limit(60*10);  // 10 mins

        $orderBy =  isset($this->request->query['order']) ? $this->request->query['order'] : '';
        $asc = isset($this->request->query['orderasc']) ? $this->request->query['orderasc'] == 'true' : true;

        //paginate results so that we dont hit the 16mb result limit.
        $variables = array();

        if (isset($this->request->query['variableStart']) && isset($this->request->query['variableEnd']))
        {
            $variables['start'] =  $this->request->query['variableStart'];
            $variables['end'] = $this->request->query['variableEnd'];
        }

        $query = $this->request->query['query'];

        $user = $this->User->findById(
            $this->PreslogAuth->user('_id')
        );

        // add a query value which ensures they only get results from their own client_id
        if ($this->isAuthorized('single-client'))
        {
            $query .= 'AND client_id = ' . $user['User']['client_id'] ;
        }

        //replace any variables that are passed in
        foreach($variables as $variable => $value)
        {
            $query = str_replace('{' . $variable . '}', $value, $query);
        }

        $clients = $this->User->listAvailableClientsForUser($user['User']);
        $fullClients = array();
        foreach($clients as $client) {
            $c = $this->Client->findById($client['_id']);
            $fullClients[] = $c['Client'];
            $options[(string)$client['_id']] = $c['Client'];
        }

        $count = $this->Log->countByQuery($query, $fullClients);
        if ( isset($return['errors']) )
        {
            $this->errorGeneric(array('data'=>$return['errors'], 'message'=>'Export failed') );
            return;
        }

        // Output is using view class
        $this->viewClass = 'View';
        $this->layout = 'ajax';


        // Instigate headers
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . date('Y-m-d') .'.xls"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: no-cache, no-store, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        // Ensures output is immediate
        ob_implicit_flush(true);
        ob_end_clean();

        // Start the file off
        echo " ";

        // Perform search
        // Returns Logs and Options to accompany
        $logs = array();
        $limit = 200;
        $start = 0;

        do
        {
            // Search for logs
            $return = $this->executeSearch( $this->request->query, $limit, $start, $orderBy, $asc, $variables);

            if ( isset($return['errors']) )
            {
                $this->errorGeneric(array('data'=>$return['errors'], 'message'=>'Export failed') );
                return;
            }

            $logs = array_merge($logs, $return['logs']);

            // Increment
            $start += $limit;

        } while ($start < $count);

        // Generate export XLS from data
        $this->set('logs', $logs);
        $this->viewClass = 'View';
        echo $this->render('export_xls');

        // Complete request
        exit();
    }

    /**
     * Search using the given query string
     *
     * @SWG\Operation(
     *      partial="search.validate",
     *      summary="Return any errors that may be in the passed in JQL query",
     *      notes="Users can only search across those Clients to which they have access.",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="query",
     *              paramType="query",
     *              dataType="string",
     *              required="true",
     *              description="jql to validate"
     *          )
     *      )
     * )
     */
    public function validateQuery()
    {
        if ( ! isset($this->request->query['query']) )
        {
            $return = array(
                'ok' => true,
                'errors' => array(),
            );

            $this->set($return);
            $this->set('_serialize', array_keys($return));
            return;
        }

        $query = $this->request->query['query'];

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

        $parser = new PreslogParser();
        $parser->setSqlFromJql($query);

        $errors = $parser->getErrors();
        if( sizeof($errors) == 0 )
        {
            $errors = $parser->validate($fullClients);
        }

        $return = array();

        if ( sizeof($errors) > 0 )
        {
            $return = array(
                'ok' => false,
                'errors' => $errors
            );
        }
        else
        {
            $result = $this->Log->findByQuery($query, $fullClients);
            if ( isset($result['ok']) && $result['ok'] === 0 )
            {
                $return = array(
                    'ok' => false,
                    'errors' => $result['errors'],
                );
            }
            else
            {
                $return = array(
                    'ok' => true,
                    'errors' => array(),
                );
            }
        }

        $this->set($return);
        $this->set('_serialize', array_keys($return));
    }

    /**
     * Perform the search operation and return a series of log data sufficient for search results.
     */
    protected function executeSearch( $params, $limit = 3, $start = 0, $orderBy = '', $orderAsc = true, $variables = array())
    {
        $options = array();

        // Validate: Search criteria must not be empty
        if ( !isset($params['query']) )
        {
            $this->errorBadRequest(array('message'=>"Search parameters must not be empty. Please supply a valid JQL query to the 'query' variable."));
        }

        // Load query
        $query = $params['query'];

        // add a query value which ensures they only get results from their own client_id
        if ($this->isAuthorized('single-client'))
        {
            $user = $this->User->findById(
                $this->PreslogAuth->user('_id')
            );

            $query .= ' AND client_id = ' . $user['User']['client_id'] ;
        }

        // replace any variables that are passed into the query
        foreach($variables as $variable => $value)
        {
            $query = str_replace('{' . $variable . '}', $value, $query);
        }

        // Fetch current user
        // TODO: This can be more efficient?
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

        // Error on query failure
        if ( isset($results['ok']) && !$results['ok'] )
        {
            return array(
                'query' => $query,
                'errors' => $results['errors'],
            );
        }

        // Get size of query results
        $total = $this->Log->countByQuery($query, $fullClients);

        $clients = array();
        //loop results for client, created by users and any other info we will need to grab for display
        foreach ($results as $k=>$result)
        {
            // Collate the list of clients for fetching the field format
            $clients[] = $result['Log']['client_id'];
        }

        //list all fields that we can use to sort these logs
        $allFieldNames = array();

        //loop through the logs again and reformat them for display
        $logs = array();
        foreach ($results as $k=>$rawLog) {

            // Fetch client entity
            $clientModel = ClassRegistry::init('Client');
            $clientEntity = $clientModel->getClientEntityById( $rawLog['Log']['client_id'] );

            // Skip clients that don't load
            if ( !$clientEntity )
            {
                continue;
            }

            // Load the log schema by the client
            $log = new LogEntity();
            $log->setDataSource( $this->Log->getDataSource() );
            $log->setClientEntity($clientEntity);

            // Interpret the log data to be saved
            $log->fromArray( $rawLog['Log'] );

            // Generate fields list
            $fields = $log->toDisplay();

            // New field list per log
            $fieldList = array();

            // Track all field names
            foreach ($fields as $key=>$value)
            {
                // Track field names
                $allFieldNames[$key] = true;

                // Convert to arrangement the client is expecting
                $logData = array(
                    'title'=>$key,
                    'value'=>$value,
                );

                // Put to field list
                $fieldList[] = $logData;

            }

            // Put to log list
            $logs[] = array(
                'id' => $rawLog['Log']['hrid'],
                'attributes'=>$fieldList
            );

        }

        // Return the Results and the corresponding Client opts
        return array('query' => $query, 'logs' => $logs,  'fields' => array_keys($allFieldNames),'total' => $total);
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

        $clients = $this->getClientListForUser();
        $fullClients = array();
        foreach($clients as $client) {
            $c = $this->Client->findById($client);
            $fullClients[] = $c['Client'];
            $options[$client] = $c['Client'];
        }


        $parser = new PreslogParser();
        $parser->setSqlFromJql($jql);
        $errors = $parser->getErrors();
        if ( sizeof($errors) == 0 )
        {
            $errors = $parser->validate($fullClients);
        }

        if ( sizeof($errors) > 0)
        {
            $this->set('errors', $errors);
            $this->set('_serialize', array('errors'));
            return;
        }


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
                'type' => 'ID',
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
        $types['ID'] = array(
            'editor' => 'TEXT',
            'name' => 'ID',
            'operators' => $this->listOperators('ID'),
        );
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
                    'name' => strtoupper($fieldSettings['name']),
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
                            'value' => strtoupper($child['name']),
                            'label' => $child['name']
                        );

                        foreach($child['children'] as $subChild)
                        {
                            if ( ! $subChild['deleted'])
                            {
                                $options[] = array(
                                    'value' => strtoupper($subChild['name']),
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
                    'name' => $operator->getSqlSymbol(),
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
            throw new Exception('invalid array of arguments');
        }

        $parser = new PreslogParser();
        $parser->setJqlFromSql($sql, $args);

        $this->set('jql', $parser->getJql());
        $this->set('args', $parser->getArguments());
        $this->set('_serialize', array('jql', 'args'));
    }

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
