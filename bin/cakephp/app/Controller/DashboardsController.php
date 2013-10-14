<?php


use Preslog\Fields\Types\TypeAbstract;
use Preslog\JqlParser\JqlParser;
use Swagger\Annotations as SWG;
use Preslog\Widgets\WidgetFactory;

/**
 * Class DashboardController
 */
class DashboardsController extends AppController
{
    public $uses = array('User', 'Dashboard', 'Widget', 'Log', 'Client');


    /**
     * Fetch the list of dashboards for this users menu
     *
     * @SWG\Operation(
     *      partial="dashboards.list",
     *      summary="List available dashboards",
     *      notes=""
     * )
     */
    public function listAllDashboards()
    {
        $this->set('preset', $this->listPresetDashboards());
        $this->set('favourites', $this->listLoggedInFavouriteDashboards());
        $this->set('dashboards', $this->listAllCustomDashboards());
        $this->set('_serialize', array('preset', 'favourites', 'dashboards'));
    }


    /**
     * given the logged in user list their favourite dashbaords
     * @return array
     */

    private function listLoggedInFavouriteDashboards() {
        $user = $this->User->findById(
            $this->PreslogAuth->user('_id'),
            array('fields'=>array(
                'favouriteDashboards',
            ))
        );

        $fav = array();
        if (isset($user['User']['favouriteDashboards'])) {
            foreach($user['User']['favouriteDashboards'] as $dashboardId) {
                if (!empty($dashboardId)) {
                    $dashboard = $this->Dashboard->findById(new MongoId($dashboardId));
                    if (!empty($dashboard)) {
                        $fav[] = array(
                            'id' => $dashboard['Dashboard']['_id'],
                            'name' => $dashboard['Dashboard']['name'],
                        );
                    }
                }
            }
        }

        return $fav;
    }

    /***
     * Retrieve a list of all custom (not preset) dashboards that exist and are shared with the current logged in users default client.
     */
    private function listAllCustomDashboards() {
        //TODO finish
        $dashboards = array();

        $dashboards = $this->Dashboard->find('all', array(
            'conditions' => array('preset' => false),
        ));

        return $dashboards;
    }

    /**
     * return a list of dashbaords that are preset by super-admin (everyone can see these)
     * @return array
     */
    private function listPresetDashboards() {
        $preset = array();

        return $preset;
    }
    /**
     * Create a new dashboard
     *
     * @SWG\Operation(
     *      partial="dashboards.create",
     *      summary="Create a new dashboard",
     *      notes=""
     * )
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.read",
     *      summary="Return data for a specific dashboard arrangement",
     *      notes="",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="dashboard_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Dashboard ID"
     *          )
     *      )
     * )
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.update",
     *      summary="Update the specified dashboard",
     *      notes="",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="dashboard_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Dashboard ID"
     *          )
     *      )
     * )
     */
    public function editDashboard()
    {
        //id of the dashboard we are working on
        $id = isset($this->request->params['pass'][0]) ? $this->request->params['pass'][0] : "";

        //read dashboard
        if ($this->request->is('get')) {
            $dashboard = $this->Dashboard->findById($id);
            if (empty($dashboard)) {
                throw new Exception('We are unable to find this dashboard in the system');
            }
            $dashboard = $this->_getParsedDashboard($dashboard['Dashboard']);
            $this->set('dashboard', $this->Dashboard->toArray($dashboard, false));
            $this->set('status', 'success');

        } else if ($this->request->is('post')) {

            //edit dashboard
            if (!empty($id)) {
                if(isset($this->request->data['widgets'])) { //just update the widgets
                    $this->updateDashboardWidgets($id, $this->request->data['widgets']);
                    $dashboard = $this->Dashboard->findById($id);

                //update the name
                } else {
                    $dashboard = $this->Dashboard->findById($id);
                    $dashboard['Dashboard']['name'] = $this->request->data['name'];
                    $this->Dashboard->save($dashboard['Dashboard']);

                    $dashboard = $this->_getParsedDashboard($dashboard['Dashboard']);
                    $this->set('dashboard', $this->Dashboard->toArray($dashboard, false));
                    $this->set('status', 'success');
                }

                $dashboard = $this->_getParsedDashboard($dashboard['Dashboard']);
                $this->set('dashboard', $this->Dashboard->toArray($dashboard));
                $this->set('status', 'saved');

            //new dashboard
            } else {
                $dashboard = array(
                    '_id' => new MongoId(),
                    'name' => $this->request->data['name'],
                    'type' => 'static',
                    'widgets' => array(),
                    'shares' => array(),
                );
                $this->Dashboard->create($dashboard);
                $this->Dashboard->save();

                $dashboard = $this->Dashboard->findById($dashboard['_id']);
                $dashboard = $this->_getParsedDashboard($dashboard);
                $this->set('dashboard', $this->Dashboard->toArray($dashboard, false));
                $this->set('status', 'created');
            }
        }

        //TODO remove this and make sure we only send a list of clients to admin users.
        $clients = $this->Client->find('all');
        $this->set('clients', $clients);

        $this->set('favourites', $this->listLoggedInFavouriteDashboards());
        $this->set('_serialize', array('status', 'dashboard', 'favourites', 'clients'));
    }

    /*
     * given a dashboard that has just come out from the database repalce all the widgets with widget objects
     */
    private function _getParsedDashboard($dashboard) {
        $widgets = array();

        if (isset($dashboard['widgets'])) {
            foreach($dashboard['widgets'] as $widget) {
                $widgetObject = null;
                if(!($widget instanceof Widget)) {
                    $widgetObject = $this->_createWidgetObject($widget);
                } else {
                    $widgetObject = $widget;
                }
                $widgets[] = $widgetObject;
            }
        }

        $dashboard['widgets'] = $widgets;
        return $dashboard;
    }

    private function updateDashboardWidgets($id, $widgets) {
        $dashboard = array(
            '_id' => new MongoId($id),
            'widgets' => array(),
        );
        foreach($widgets as $widget) {
            $widgetObject = $this->_createWidgetObject($widget);
            $dashboard['widgets'][] = $widgetObject->toArray();
        }

        $this->Dashboard->save($dashboard, false, array(
            '_id',
            'widgets',
        ));
        return $dashboard;
    }

    /**
     * Delete the specific dashboard by ID
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.delete",
     *      summary="Delete the specified dashboard",
     *      notes="",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="dashboard_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Dashboard ID"
     *          )
     *      )
     * )
     */
    public function deleteDashboard($dashboardId)
    {
        $this->Dashboard->delete(new MongoId($dashboardId));

        $this->set('delete', 'success');
        $this->set('_serialize', array('delete'));
    }


    /**
     * Fetch a specific dashboard widget (construct and/or data) for display
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.widgets.create",
     *      summary="Create a new widget on the specified dashboard",
     *      notes="",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="dashboard_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Dashboard ID"
     *          )
     *      )
     * )
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.widgets.specific.read",
     *      summary="Fetch data for a specified widget",
     *      notes="",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="dashboard_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Dashboard ID"
     *          ),
     *          @SWG\Parameter(
     *              name="widget_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Widget ID"
     *          )
     *      )
     * )
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.widgets.specific.update",
     *      summary="Update the specified widget",
     *      notes="",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="dashboard_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Dashboard ID"
     *          ),
     *          @SWG\Parameter(
     *              name="widget_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Widget ID"
     *          )
     *      )
     * )
     */
    public function editWidget()
    {
        $dashboard = $this->Dashboard->findById(new MongoId($this->request->params['dashboard_id']));
        $dashboard = $dashboard['Dashboard'];
        $success = false;

        $serialize = array('success');
        $widget = null;
        if ($this->request->is('post'))
        {
            //create widget
            if (!isset( $this->request->params['widget_id'])) {
                $widget = $this->_createWidgetObject($this->request->data['widget']);
                $dashboard['widgets'][] = $widget->toArray(true);
                $this->Dashboard->save($dashboard);
                $this->set('widget', $widget->toArray(false));
                $serialize[] = 'widget';

                $success = true;
            } else { //edit widget
                $widgetArrayId = $this->Dashboard->findWidgetArrayId($dashboard, $this->request->params['widget_id']);
                $widget = $this->_createWidgetObject($this->request->data['widget']);
                $dashboard['widgets'][$widgetArrayId] = $widget->toArray(true);
                   // $this->Widget->updateWidget($dashboard['widgets'][$widgetArrayId], );
                $this->Dashboard->save($dashboard);

                $this->set('widget', $widget->toArray(false));
                $serialize[] = 'widget';

                $success= true;
            }
        }

        if ($this->request->is('get')) { //read widget
            $widgetArrayId = $this->Dashboard->findWidgetArrayId($dashboard, $this->request->params['widget_id']);
            $widget = $this->_createWidgetObject($dashboard['widgets'][$widgetArrayId]);
            $this->set('widget', $widget->toArray(false));
            $serialize[] = 'widget';
        }

        $this->set('success', $success);
        $this->set('_serialize', $serialize);
    }

    /**
     * Delete a widget on a dashboard
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.widgets.specific.delete",
     *      summary="Deletes the specified widget",
     *      notes="",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="dashboard_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Dashboard ID"
     *          ),
     *          @SWG\Parameter(
     *              name="widget_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Widget ID"
     *          )
     *      )
     * )
     */
    public function deleteWidget($dashboardId, $widgetId)
    {
        $dashboard = $this->Dashboard->findById(new MongoId($dashboardId));
        $dashboard['Dashboard']['_id'] = new MongoId($dashboardId);
        foreach($dashboard['Dashboard']['widgets'] as $key => $widget) {
            if ($dashboard['Dashboard']['widgets'][$key]['_id'] == $widgetId) {
                unset($dashboard['Dashboard']['widgets'][$key]);
                break;
            }
        }

        $this->Dashboard->save($dashboard['Dashboard']);
        $this->set('success', true);
        $this->set('dashboard', $dashboard['Dashboard']);
        $this->set('_serialize', array('success', 'dashboard'));
    }


    /**
     * Export the given dashboard as a Word Document report
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.export",
     *      summary="Download DOCX export of the specified dashboard",
     *      notes="User must be an Administrator. Instigates a download of the Dashboard as a DOCX",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="dashboard_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Dashboard ID"
     *          )
     *      )
     * )
     */
    public function exportDashboard($dashboardId)
    {
        $dashboard = $this->Dashboard->findById($dashboardId);
        $reportName = 'report_' . $dashboard['Dashboard']['name'] . '.docx';
        $reportPath = $this->Dashboard->generateReport($dashboard['Dashboard'], $reportName);

        $this->response->file($reportPath, array(
            'download' => true,
            'name' => $reportName
        ));
        return $this->response;
    }


    /**
     * Export the given dashboard widgets source data as an XLS file
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.widgets.specific.export",
     *      summary="Download the given Widget as an XLS",
     *      notes="Instigates the download of an XLS containg logs used to compile this widget.",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="dashboard_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Dashboard ID"
     *          ),
     *          @SWG\Parameter(
     *              name="widget_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Widget ID"
     *          )
     *      )
     * )
     */
    public function exportWidget()
    {
        // TODO
        $this->set('todo', 'Export Widget');
        $this->set('_serialize', array('todo'));
    }


    /**
     * Read the list of this users favourite dashboards
     *
     * @SWG\Operation(
     *      partial="dashboards.favourites.read",
     *      summary="Read the list of this users favourite dashboards",
     *      notes="User must be logged in."
     * )
     */
    public function listFavouriteDashboards()
    {
        $this->set('favourites', $this->listLoggedInFavouriteDashboards());
        $this->set('_serialize', array('favourites'));
    }


    /**
     * Add to the list of this users favourite dashboards
     *
     * @SWG\Operation(
     *      partial="dashboards.favourites.update",
     *      summary="Submit a new dashboard to add to this users favourites",
     *      notes="User must be logged in."
     * )
     */
    public function editFavouriteDashboards()
    {
        //DELETE will pass it in
        if(isset($this->request->pass['dashboard_id'])) {
            $dashboardId = $this->request->pass['dashboard_id'];
        }

        //POST will be params of request
        if (empty($dashboardId)) {
            $dashboardId = $this->request->data('dashboard_id');
        }

        $user = $user = $this->User->findById(
            $this->PreslogAuth->user('_id')
        );

        //make sure this dashboard is not all ready a favourite for the user;
        $found = false;
        foreach($user['User']['favouriteDashboards'] as $key => $dashboard) {
            if ($dashboard == $dashboardId) {
                $found = true;

                //user requested we delete this one.
                if ($this->request->is('delete')) {
                    unset($user['User']['favouriteDashboards'][$key]);
                }
            }
        }

        //only add the dashboard if we did not find it
        if (!$found) {
            $user['User']['favouriteDashboards'][] = $dashboardId;
            $this->User->save($user['User']);
        }

        $this->set('favourites', $this->listLoggedInFavouriteDashboards());
        $this->set('_serialize', array('favourites'));
    }


    /**
     * Delete a favourite dashboard from the favourites list
     *
     * @SWG\Operation(
     *      partial="dashboards.favourites.delete",
     *      summary="Delete a favourite dashboard from the dashboard list",
     *      notes="User must be logged in."
     * )
     */
    public function deleteFavouriteDashboards()
    {
        // TODO
        $this->set('todo', 'Delete Favourite Dashboards');
        $this->set('_serialize', array('todo'));
    }

    /**
     * Fetch the list of available dashboard widgets
     *
     * @SWG\Operation(
     *      partial="widgets.list",
     *      summary="List all available widget types",
     *      notes=""
     * )
     */
    public function listWidgets()
    {
        // TODO
        $this->set('todo', 'List Widgets');
        $this->set('_serialize', array('todo'));
    }


    /**
     * Fetch options for this particular widget type
     *
     * @SWG\Operation(
     *      partial="widgets.options",
     *      summary="Fetch options for the specified widget type",
     *      notes="",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="widget_type",
     *              paramType="path",
     *              dataType="string",
     *              required="true",
     *              description="Widget Type"
     *          )
     *      )
     * )
     */
    public function readWidgetOptions()
    {
        // TODO
        $this->set('todo', 'Read Widget Options');
        $this->set('_serialize', array('todo'));
    }

    /*
     * Create an instance of Preslog\Widget and populate it with data passed in
     */
    private function _createWidgetObject($widget) {
        $widgetObject = WidgetFactory::createWidget($widget);
        //set the id if we have it otherwise it will be random
        if (isset($widget['_id'])) {
            $widgetObject->setId(new MongoId($widget['_id']));
        }

        //TODO replace with client list from session
        $clients = array();
        $allClients = $this->Client->find('all');
        foreach($allClients as $c) {
            $clients[] = $c['Client']['_id'];
        }
        //end todo

        //populate the options available for this client on this widget
        $options = $widgetObject->getOptions();

        //this gets populated in _populateOptions
        $mongoPipeLine = array();

        //populate any options that are available
        foreach($options as $optionName => $value) {
            $widgetObject = $this->_populateOptions($options, $optionName, $widgetObject, $mongoPipeLine);
        }

        return $this->_populateSeries($widgetObject, $mongoPipeLine);
    }

    /***
     * this will take all the fields each widget can use for aggregation (eg: xaxis and yaxis) create a human readable
     * list of options to display and add details of the actually selected values into the mongo pipeline
     *
     * @param $options
     * @param $optionName
     * @param $widgetObject
     * @param $mongoPipeLine
     * @return mixed
     */
    private function _populateOptions($options, $optionName, $widgetObject, &$mongoPipeLine) {
        //check this widget contains the option we want to populate
        if (!isset($options[$optionName])) {
            return $widgetObject;
        }

        $options = $options[$optionName];

        //TODO replace with client list from session
        $clients = array();
        $allClients = $this->Client->find('all');
        foreach($allClients as $c) {
            $clients[] = $c['Client']['_id'];
        }
        //end todo

        if (!empty($options)) {
            $xOptions = array();
            $xAxis = $widgetObject->getDetail($optionName);

            $xName = null;
            $xOperation = null;
            if (!empty($xAxis)) {
                $xParts = explode(':', $xAxis);
                $xName = $xParts[0];
                $xOperation = $xParts[1];
            }

            $mongoPipeLine[$optionName] = array();

            foreach($options as $option) {
                if ($option['fieldType'] instanceof TypeAbstract) {
                    //find all fields for all clients of this type
                    foreach($clients as $clientId) {
                        $client = $this->Client->findById($clientId);
                        //find the format of this type
                        foreach($client['Client']['format'] as $format) {
                            //find the format and add to options used for dropdowns
                            if ($format['type'] == $option['fieldType']->getProperties('alias')) {
                                $xOptions[$format['name']] = $option['fieldType']->listDetails($format['name']);
                            }

                            //find the type the widget is using
                            if($xName == $format['name']) {
                                $aggregationDetails = $option['fieldType']->getProperties('aggregationDetails');
                                $mongoPipeLine[$optionName][$format['name']] = $aggregationDetails[$xOperation];
                            }
                        }
                    }
                } else {
                    //these are mostly hardcoded and computed fields
                    switch ($option['fieldType']) {
                        case 'count':
                            $xOptions['count'] = array(
                                array(
                                    'name' => 'Count Logs',
                                    'id' => 'count:count',
                                ),
                            );

                            if ($xName == 'count') {
                                $mongoPipeLine[$optionName]['count'] = array(
                                    'dataLocation' => 1,
                                    'groupBy' => '$sum',
                                    'aggregate' => true,
                                );
                            }
                            break;
                        case 'created':
                            break;
                        case 'modified':
                            break;
                        case 'client':
                            $xOptions['client'] = array(
                                array(
                                    'name' => 'by Client',
                                    'id' => 'client:client',
                                ),
                            );

                            if ($xName == 'client') {
                                $mongoPipeLine[$optionName]['client'] = array(
                                    'dataLocation' => 'client_id',
                                    'groupBy' => '',
                                    'aggregate' => false,
                                );
                            }
                            break;
                    }

                }
            }

            $show = array();
            foreach($xOptions as $field) {
                foreach($field as $option) {
                    $show[] = $option;
                }
            }

            $widgetObject->setDisplayOptions($optionName, $show);
        };

        return $widgetObject;
    }


    /***
     * run any queries needed against the database to populate the data on for the widget
     * -this will parse the jql text query into a mongo $match array (via JqlParser)
     * -find any fields in the query and get the corrosponding mongoId for each client that is available to this user.
     * -send the $match to the correct find (aggregation or normal) and add the returned data onto the widget object
     *
     * @param $widgetObject
     * @param $aggregationPipeLine
     *
     * @throws Exception
     * @return mixed
     */
    private function _populateSeries($widgetObject, $aggregationPipeLine) {

        // Translate query to Mongo
        $jqlParser = new JqlParser();
        $jqlParser->setSqlFromJql($widgetObject->getDetail('query'));
        $match = $jqlParser->getMongoCriteria();

        //TODO replace with client list from session
        $clients = array();
        $allClients = $this->Client->find('all');
        foreach($allClients as $c) {
            $clients[] = $c['Client']['_id'];
        }
        //end todo

        //find which fields we are using in the query
        $fieldNames = $jqlParser->getFieldList();

        //add the fields that are being used for grouping (aggregation)
        foreach($aggregationPipeLine as $fieldName => $fields) {
            foreach($fields as $name => $value) {
                $fieldNames[] = $name;
            }
        }

        //get the id's for the fields from each available client
        $fields = array();
        foreach ($clients as $clientId) {
            $client = $this->Client->findById($clientId);
            foreach($client['Client']['format'] as $format) {
                //does this client have a field with this name?
                if (in_array($format['name'], $fieldNames)) {
                    if (!isset($fields[$format['name']])) {
                        $fields[$format['name']] = array();
                    }
                    //add the id for this cleints version of the field.
                    $fields[$format['name']][] =  new MongoId($format['_id']);
                }
            }
        }

        $result = array(
            'ok' => 0,
        );

        //send to database and get results.
        if ($widgetObject->isAggregate()) {
           $result = $this->Log->findAggregate($match, $aggregationPipeLine, $fields);
        } else {
            $result = $this->Log->findByQuery($match);
        }

        if (empty($result)) {
            //TODO why is it empty??????
            $widgetObject->setSeries(array());
        } else {
            if ($result['ok'] != 1) {
                throw new Exception('query to mongo failed!!!!'); //TODO replace with cake exception
            }

            //remove any mongo'ids from series to show field value
            $seriesTypeDetails = explode(':', $widgetObject->getDetail('series'));
            $seriesType = $seriesTypeDetails[0];
            $dataLocation = '';
            $parsedResult = array();
            foreach($widgetObject->getOptions()['series'] as $option) {
                $fieldType = $option['fieldType'];
                if ($fieldType instanceof TypeAbstract) {
                    if ($fieldType->getProperties('alias') == $seriesTypeDetails[1]) {
                        $seriesType = $fieldType;
                        $aggregationDetails = $fieldType->getProperties('aggregationDetails');
                        foreach($aggregationDetails as $name => $details) {
                            if ($name = $seriesTypeDetails[1]) {
                                $dataLocation = $details['dataLocation'];
                            }
                        }
                    }
                }
            }

            if ($seriesType instanceof TypeAbstract) {
                $parsedPoint = array();
                foreach($result['result'] as $point) {
                    $parsedPoint = $point;
                    if ($point['series'][$dataLocation] instanceof MongoId) {
                        foreach($allClients as $client) {
                            foreach($client['Client']['format'] as $format) {

                                //it is some kind of select so search through the options for the value
                                if (isset($format['data']) && isset($format['data']['options'])) {
                                    foreach($format['data']['options'] as $option) {
                                        if ($option['_id'] == $point['series'][$dataLocation]) {
                                            $parsedPoint['series'] = $option['name'];
                                        }
                                    }
                                } else if ($format['_id'] == $point['series']) { //client
                                    $parsedPoint['series'] = $client['Client']['name'];
                                }
                            }
                        }
                    }
                    $parsedResult[] = $parsedPoint;
                }

            } else {
                switch ($seriesType) {
                    case 'client':
                        foreach($result['result'] as $point) {
                            if ($point['series'] instanceof MongoId) {
                                $client = $this->Client->findById($point['series']);
                                $point['series'] = $client['Client']['name'];
                            }
                            $parsedResult[] = $point;
                        }

                        break;
                }
            }

            $widgetObject->setSeries($parsedResult);

        }

        return $widgetObject;
    }
}
