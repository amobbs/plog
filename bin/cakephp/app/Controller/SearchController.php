<?php

App::uses('AppController', 'Controller');

use Swagger\Annotations as SWG;
use Preslog\JqlParser\JqlOperator\JqlOperator;
use Preslog\JqlParser\JqlParser;
use Preslog\PreslogParser\PreslogParser;
use Preslog\Logs\Entities\LogEntity;

/**
 * Class SearchController
 * @property    Log     Log
 * @property    Client  Client
 * @property    User    User
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
     *              type="string",
     *              required="true",
     *              description="jql to search on"
     *          )
     *      )
     * )
     */
    public function search()
    {
        // Prep the search criteria
        $search = $this->prepareSearchCriteria();

        // Perform search
        // Returns Logs and Options to accompany
        $return = $this->executeSearch( $search['query'], $search['limit'], $search['start'], $search['orderBy'], $search['asc'], $search['variables']);

        // used for dashboards to determin which widget we are updating.
        if ( isset($this->request->query['widgetid']) )
        {
            $return['widgetid'] = $this->request->query['widgetid'];
        }

        // Return search result
        $this->set($return);
        $this->set('_serialize', array_keys($return));
    }


    /**
     * Prepare the search criteria
     * Shared functionality between Search and Export
     */
    protected function prepareSearchCriteria()
    {
        // Create empty search if not set
        $query = array();
        if (isset($this->request->query['query']))
        {
            $query = "deleted = false";
            $query .= empty($this->request->query['query']) ? "" : ' and ' . $this->request->query['query'];

        }

        // Build search query params
        $search = array(
            'query'     => $query,
            'limit'     => (isset($this->request->query['limit']) ? $this->request->query['limit'] : 3),
            'start'     => (isset($this->request->query['start']) ? $this->request->query['start'] : 1),
            'orderBy'   => (isset($this->request->query['order']) ? $this->request->query['order'] : 'Created'),
            'asc'       => (isset($this->request->query['orderasc']) ? $this->request->query['orderasc'] == 'true' : false),
            'variables' => array(),
        );

        // List of vars available in search
        $variableList = array(
            'variableStart' =>array('type'=>'date'),
            'variableEnd'   =>array('type'=>'date'),
        );

        // Parse variables
        foreach ($variableList as $key=>$variable)
        {
            // Is this var available?
            if (isset($this->request->query[$key]))
            {
                $value = $this->request->query[$key];

                // Convert to datetime
                if ($variable['type'] == 'date')
                {
                    // Only convert if strtotime can do something useful with it
                    if (!is_numeric($value))
                    {
                        $log = Logger::getLogger(__CLASS__);
                        $log->info('non numeric Date type variable found, converting to time. from [' . $value . '] to [' . strtotime($value) . ']');
                        $value = strtotime($value);
                    }
                }

                //remove the word variable from the start
                $variableName = strtolower(substr($key, 8));
                // Save to vars list
                $search['variables'][ $variableName ] = $value;
            }
        }

        // replace any variables that are passed into the query
        foreach( $search['variables'] as $variable => $value)
        {
            $search['query'] = str_replace('{' . $variable . '}', $value, $search['query']);
        }

        // Done
        return $search;
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
     *              type="string",
     *              required="true",
     *              description="jql to search on"
     *          )
     *      )
     * )
     */
    public function export()
    {
        // Export is allowed to continue until it's done.
        set_time_limit(0);
        @ini_set('memory_limit', '64M');

        // Prepare criteria
        $search = $this->prepareSearchCriteria();

        $clientObjs = $this->Client->find('all');
        $clients = array();
        foreach($clientObjs as $c)
        {
            $clients[] = $c['Client'];
        }

        // convert string from jql to mongo array
        $parser = new PreslogParser();
        $parser->setSqlFromJql($search['query']);
        $errors = $parser->getErrors();
        if ( sizeof($errors) !== 0)
        {
            $errors = $parser->validate($clients);

            $this->errorBadRequest(array('message'=>'There was a problem with your query. Please try again.', 'data'=>$errors));
        }

        // Fetch the match query
        $match = $parser->parse($clients);

        // Add to search criteria
        $criteria = array();
        if ( !empty($match))
        {
            $criteria[] = array('$match'=>$match);
        }

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
        echo "\n";

        // Build the aggregate pipeline
        // Unwind fields and attributes, then add-to-set into a big array of both.
        $criteria[] = array('$unwind' => '$fields');
        $criteria[] = array('$unwind' => '$attributes');
        $criteria[] = array('$group'  => array(
            '_id'       => '1',
            'clients'   =>array('$addToSet'=>'$client_id'),
            'fields'    =>array('$addToSet'=>'$fields.field_id'),
            'attributes'=>array('$addToSet'=>'$attributes'),
        ));
        $mongo = $this->Log->getMongoDb();
        $fieldResult = $mongo->selectCollection('logs')->aggregate($criteria);
        $fieldResult = $fieldResult['result'][0];

        // Aggregate the Client list to fetch the friendly names of fields in this query
        // Match clients first for efficient find
        // Unwind fields
        // Match on the fields._id to restrict to ones showing up in the export.
        $criteria = array(
            array('$match'  => array('_id'=>array('$in'=>$fieldResult['clients']))),
            array('$project'=> array('field'=>'$fields')),
            array('$unwind' => '$field'),
            array('$match'  => array('field._id'=>array('$in'=>$fieldResult['fields']))),
            array('$sort'   => array('client_id'=>-1, 'field.order'=>1)),
        );
        $clientFieldResult = $mongo->selectCollection('clients')->aggregate($criteria);

        // Aggregate the Client list to fetch the friendly Attribute group names for each client
        // Match the client first
        // Unwind the attributes
        $criteria = array(
            array('$match'  => array('_id'=>array('$in'=>$fieldResult['clients']))),
            array('$project'=> array('field'=>'$attributes')),
            array('$unwind' => '$field'),
            array('$sort'   => array('client_id'=>1, 'field.order'=>1)),
        );
        $clientAttributeResult = $mongo->selectCollection('clients')->aggregate($criteria);

        // :WARN: There's a potential bug here.
        // If the Client has two fields (named A and B) with the same label (X and X) then the nameMap
        // will fail to align A and B separately, they'll instead both go into A, and B will be empty.
        // This can only be fixed if we modify toDisplay, or the executeSearch function to return more data.
        // Similarly, an Attribute with the same Label and a Field will cause this problem.

        // Combine both result sets
        $sourceFields = array_merge( array_values($clientFieldResult['result']), array_values($clientAttributeResult['result']) );
        $finalFields = array(
            0=>array('field'=>array(
                'name'=>'id',
                'label'=>'ID',
            ))
        );

        // Locate "LogInfo" type field, and expand to individual field references
        foreach ($sourceFields as $pos=>$field)
        {
            // Type is loginfo?
            if (isset($field['field']['type']) && $field['field']['type'] == 'loginfo')
            {
                // Duplicate, with the appropriate labels, and splice into the array
                $splice = array();
                $labels = array(
                    'loginfo.created'       =>'Created',
                    'loginfo.created_user'  =>'Created By',
                    'loginfo.modified'      =>'Modified',
                    'loginfo.modified_user' =>'Modified By',
                    'loginfo.version'       =>'Version'
                );

                // Replace names and labels
                foreach ($labels as $name=>$label)
                {
                    $field['field']['name'] = $name;
                    $field['field']['label'] = $label;
                    $finalFields[] = $field;
                }
            }
            else
            {
                $finalFields[] = $field;
            }
        }


        // Collate Field and then Attribute list
        $fieldList = array();           // List of unique fields, for use as labels/headings
        $labelToPositionMap = array();  // Map Field.Label to position

        $pos = 0;
        foreach ($finalFields as $field)
        {
            $field = $field['field'];

            // If the name isn't already set, this is a new field.
            if (!isset($fieldList[ $field['name'] ]))
            {
                // Map unique fields by name
                $fieldList[ $field['name'] ] = array(
                    'label'     => $field['label'],
                    'name'      => $field['name'],
                    'position'  => $pos,
                );

                // increment position
                $pos++;
            }

            // Map the label of this field to a position
            $labelToPositionMap[ $field['label'] ] = $fieldList[ $field['name'] ]['position'];
        }


        // Perform search
        // Returns Logs and Options to accompany
        $search['limit'] = 200;
        $search['start'] = 0;

        do
        {
            // Search for logs
            $return = $this->executeSearch( $search['query'], $search['limit'], $search['start'], $search['orderBy'], $search['asc'], $search['variables']);

            // Load view
            // :BUGFIX: View is created independently, as loading the view via the controller
            // creates a circular reference, resulting in a memory leak for each loop of this renderer.
            // See http://paul-m-jones.com/archives/262
            $view = new View();
            $view->layout = 'ajax';

            // Generate export XLS from data
            $view->set('logs', $return['logs']);
            $view->set('map', $labelToPositionMap);
            $view->set('headings', false);

            // Show heading?
            if (0 == $search['start'])
            {
                $view->set('headings', $fieldList);
            }

            // Render
            echo $view->render('Search/export_xls');

            // Increment limits
            $search['start'] += $search['limit'];

            unset($view);

        } while (isset($return['logs']) && sizeof($return['logs']));

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
     *              type="string",
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

        $return = array(
            'ok' => true,
            'errors' => array(),
        );

        if ( sizeof($errors) > 0 )
        {
            $return = array(
                'ok' => false,
                'errors' => $errors
            );
        }

        $this->set($return);
        $this->set('_serialize', array_keys($return));
    }


    /**
     * Reformat duration (seconds) into H:M:S
     * @param $duration
     * @return string
     */
    protected function executeSearch( $query, $limit = 3, $start = 0, $orderBy = '', $orderAsc = true, $variables = array())
    {
        $options = array();

        // add a query value which ensures they only get results from their own client_id
        if ($this->isAuthorized('single-client'))
        {
            $user = $this->User->findById(
                $this->PreslogAuth->user('_id')
            );

            $query .= ' AND client_id = ' . $user['User']['client_id'] ;
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
                //skip create/modified by. it wasn't specifically in the spec and we have not hard coded it into the jql parser. this will remove it from the sort by drop down.
                //RAPID-6736 - added back in so they can view this in excel export. the hard coding into jqlParser only searches on mongo id so needs some work.
//                if ($key == 'Created By' || $key == 'Modified By')
//                {
//                    continue;
//                }

                //skip attributes as these are a group of values and it does not make sense to sort based on them.
                if ( ! $clientEntity->isAttributeLabel($key))
                {
                    // Track field names
                    $allFieldNames[$key] = true;
                }

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

    /**
     * Fetch username from user._id
     * @param $id
     * @param $users
     * @return string
     */
    private function _getUserName($id, $users) {
        foreach($users as $user) {
            if ($user['User']['_id'] == $id) {
                return $user['User']['firstName'] . ' ' . $user['User']['lastName'];
            }
        }

        return '';
    }

    /**
     * Fetch client Company from client._id
     * @param $clientId
     * @param $clients
     * @return string
     */
    private function _getCompanyName($clientId, $clients) {
        foreach($clients as $client) {
            if ($client['_id'] == (string)$clientId) {
                return $client['Client']['name'];
            }
        }

        return '';
    }


    /**
     * Find field within the client, by the fields._id
     * @param $fieldId
     * @param $clientFields
     * @return bool
     */
    private function _getFieldFromClientById($fieldId, $clientFields) {
        foreach($clientFields['fields'] as $field) {
            if ($field['_id'] == $fieldId) {
                return $field;
            }
        }

        return false;
    }

    /**
     * Fetch the selected option name from the field list, given the option._id and options list.
     * @param $optionId
     * @param $options
     * @return mixed
     */
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
     *              type="string",
     *              required="true",
     *              description="jql that will be returned as sql to be displayed by red query builder"
     *          ),
     *          @SWG\Parameter(
     *              name="args",
     *              paramType="query",
     *              type="string",
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


    /**
     * Meta-data for query builder
     * @return array
     */
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
            'Client' => array(
                'name' => 'CLIENT',
                'label' => 'Client',
                'type' => 'CLIENT',
            )
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
        $types['CLIENT'] = array(
            'editor' => 'SELECT',
            'name' => 'CLIENT',
            'operators' => $this->listOperators('SELECT'),
        );
        $types['CHECKBOX'] = array(
            'editor' => 'SELECT',
            'name' => 'CHECKBOX',
            'operators' => $this->listOperators('SELECT'),
        );

        $clientOptions = array();

        $selectOptions = array(); //list of options that are available for SELECt types
        foreach($clientIds as $id)
        {
            $fieldTypeName= '';
            $clientEntity = $this->Client->getClientEntityById($id);

            $clientOptions[] = $clientEntity->data['name'];

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
                    $fieldTypeName = strtoupper($title);
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
                            'value' => $option['name'],
                            'label' => $option['name']
                        );
                    }
                    $selectOptions[strtoupper($fieldSettings['name'])] = $options;
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
        $selectOptions['CLIENT'] = $clientOptions;


        $selectOptions['CHECKBOX'] = array(
            array(
                'value' => 'Yes',
                'label' => 'Yes'
            ),
            array(
                'value' => 'No',
                'label' => 'No'
            ),

        );


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
     *              type="string",
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


    /**
     * List operators available to query builder
     * @param $type
     * @return array
     */
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
     *              type="string",
     *              required="true",
     *              description="sql from redquery builder will be out put as jql"
     *          ),
     *          @SWG\Parameter(
     *              name="args",
     *              paramType="query",
     *              type="string",
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

        // Check args
        if ($args === null || !is_array($args)) {
            $type = gettype($args);
            $this->errorBadRequest(array('message'=>"Query translator expected 'args' to be an array, received '$type'."));
        }

        $parser = new PreslogParser();
        $parser->setJqlFromSql($sql, $args);

        $this->set('jql', $parser->getJql());
        //$this->set('args', $parser->getArguments());      // Not required - JQL has the args
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
