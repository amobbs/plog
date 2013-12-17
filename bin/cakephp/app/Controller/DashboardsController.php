<?php

use Swagger\Annotations as SWG;
use Preslog\Logs\FieldTypes\FieldTypeAbstract;
use Preslog\Widgets\Types\BenchmarkWidget;
use Preslog\Widgets\Types\DateWidget;
use Preslog\Widgets\Types\ListWidget;
use Preslog\Widgets\WidgetFactory;
use Preslog\Widgets\Widget;

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
                    $dashboard = $this->Dashboard->findById($dashboardId);
                    if (!empty($dashboard)) {
                        $fav[] = array(
                            '_id' => $dashboard['Dashboard']['_id'],
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
        $clientIds = $this->getClientListForUser();

        $dashboards = $this->Dashboard->find('all', array(
            'conditions' => array(
                'preset' => false,
                'shares' => array('$in' => $clientIds),
            ),
        ));

        $result = array();
        foreach($dashboards as $dashboard) {
            $result[] = array(
                '_id' => $dashboard['Dashboard']['_id'],
                'name' => $dashboard['Dashboard']['name'],
            );
        }

        return $result;
    }

    /**
     * return a list of dashbaords that are preset by super-admin (everyone can see these)
     * @return array
     */
    private function listPresetDashboards() {
        $preset = array();

        $dashboards = $this->Dashboard->find('all', array(
            'conditions' => array('preset' => true),
        ));

        foreach($dashboards as $dashboard) {
            $preset[] = array(
                'name' => $dashboard['Dashboard']['name'],
                '_id' => $dashboard['Dashboard']['_id'],
            );
        }

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
     *              type="integer",
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
     *              type="integer",
     *              required="true",
     *              description="Dashboard ID"
     *          )
     *      )
     * )
     */
    public function editDashboard()
    {
        set_time_limit(60*10);  // 10 mins

        //id of the dashboard we are working on
        $id = isset($this->request->params['pass'][0]) ? $this->request->params['pass'][0] : "";

        //read dashboard
        if ($this->request->is('get')) {
            $dashboard = $this->Dashboard->findById($id);
            if (empty($dashboard)) {
                throw new Exception('We are unable to find this dashboard in the system');
            }

            $dashboard = $this->_getParsedDashboard($dashboard['Dashboard'], false);
            $this->set('dashboard', $this->Dashboard->toArray($dashboard, false));
            $this->set('status', 'success');

        } else if ($this->request->is('post')) {

            //edit dashboard
            if (!empty($id))
            {
                $dashboard = $this->Dashboard->findById($id);
                if (empty($dashboard))
                {
                    throw new Exception('We are unable to find this dashboard in the system');
                }

                if ( $dashboard['Dashboard']['preset'] )
                {
                    throw new Exception('Unable to edit Preset Dashboards');
                }

                if(isset($this->request->data['widgets'])) { //just update the widgets
                    $this->updateDashboardWidgets($id, $this->request->data['widgets']);
                    $dashboard = $this->Dashboard->findById($id);

                    $dashboard = $this->_getParsedDashboard($dashboard['Dashboard']);


                    $this->set('dashboard', $this->Dashboard->toArray($dashboard));
                    $this->set('status', 'saved');

                //update the name
                } else {
                    if (isset($this->request->data['name']))
                    {
                        $dashboard['Dashboard']['name'] = $this->request->data['name'];
                    }

                    if (isset($this->request->data['shares']))
                    {
                        $dashboard['Dashboard']['shares'] = $this->request->data['shares'];
                    }

                    $this->Dashboard->save($dashboard['Dashboard']);

                    $dashboard = $this->_getParsedDashboard($dashboard['Dashboard']);
                    $this->set('dashboard', $this->Dashboard->toArray($dashboard, false));
                    $this->set('status', 'success');
                }
            }
            //new dashboard
            else
            {

                $dashboard = array(
                    '_id' => new MongoId(),
                    'name' => $this->request->data['name'],
                    'type' => 'static',
                    'widgets' => array(),
                    'shares' => $this->_getShares(),
                    'preset' => false, //users can not create preset dashboards.
                );

                $dateWidget = WidgetFactory::createWidget(array('type' => 'date'));
                $dashboard['widgets'][] = $dateWidget->toArray();

                $this->Dashboard->create($dashboard);
                $this->Dashboard->save();

                $dashboardResult = $this->Dashboard->findById($dashboard['_id']);
                $dashboard = $this->_getParsedDashboard($dashboardResult['Dashboard']);
                $this->set('dashboard', $this->Dashboard->toArray($dashboard, false));
                $this->set('status', 'created');
            }
        }

        $clients = array();
        if ($this->isAuthorized('admin'))
        {

            $clients = $this->Client->find('all');
        }
        $this->set('clients', $clients);

        $this->set('favourites', $this->listLoggedInFavouriteDashboards());
        $this->set('_serialize', array('status', 'dashboard', 'favourites', 'clients'));
    }

    private function _getShares() {
        if (!isset( $this->request->data['shares'])) {
            return array();
        }

        $shares = $this->request->data['shares'];

        $user = $this->User->findById(
            $this->PreslogAuth->user('_id')
        );

        //share to users default client if it is not already
        $found = false;
        foreach($shares as $share) {
            if ($share == $user['User']['client_id']) {
                $found = true;
            }
        }
        if (!$found) {
            $shares[] = $user['User']['client_id'];
        }

        return $shares;
    }

    /*
     * given a dashboard that has just come out from the database replace all the widgets with kust widget ids
     */
    private function _getParsedDashboard($dashboard, $populate = true, $populateLists = false, $variables = array()) {
        $widgets = array();

        if (isset($dashboard['widgets'])) {

            //find if this has a date widget
            foreach($dashboard['widgets'] as $widget)
            {
                if (empty($variables) && $widget['type'] == 'date' && isset($widget['details']['start']) && isset($widget['details']['end']))
                {
                    $variables['start'] = $widget['details']['start'];
                    $variables['end'] = $widget['details']['end'];
                    break;
                }
            }

            foreach($dashboard['widgets'] as $widget) {
                $widgetObject = null;
                if(!($widget instanceof Widget))
                {
                    $widgetObject = $this->_createWidgetObject($widget, $variables, $populate, $populateLists);
                    if (! ($widgetObject instanceof Widget))
                    {
                        throw new Exception($widgetObject['errors'][0]);
                    }
                }
                else
                {
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
     *              type="integer",
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
     *              type="integer",
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
     *              type="integer",
     *              required="true",
     *              description="Dashboard ID"
     *          ),
     *          @SWG\Parameter(
     *              name="widget_id",
     *              paramType="path",
     *              type="integer",
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
     *              type="integer",
     *              required="true",
     *              description="Dashboard ID"
     *          ),
     *          @SWG\Parameter(
     *              name="widget_id",
     *              paramType="path",
     *              type="integer",
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

        $variables = array();
        if (isset($this->request->query['start']) && isset($this->request->query['end']))
        {
            $variables['start'] = $this->request->query['start'];
            $variables['end'] = $this->request->query['end'];
        }
        else
        {
            foreach($dashboard['widgets'] as $widget)
            {
                if ($widget['type'] == 'date' && isset($widget['details']['start']) && isset($widget['details']['end']))
                {
                    $variables['start'] = $widget['details']['start'];
                    $variables['end'] = $widget['details']['end'];
                    break;
                }
            }
        }

        $serialize = array('success');
        $widget = null;
        if ($this->request->is('post'))
        {
            //create widget
            if (!isset( $this->request->params['widget_id'])) {
                $widget = $this->_createWidgetObject($this->request->data['widget'], $variables);
                $dashboard['widgets'][] = $widget->toArray(true);
                $this->Dashboard->save($dashboard);
                $this->set('widget', $widget->toArray(false));
                $serialize[] = 'widget';

                $success = true;
            } else { //edit widget
                $widgetArrayId = $this->Dashboard->findWidgetArrayId($dashboard, $this->request->params['widget_id']);
                $widget = $this->_createWidgetObject($this->request->data['widget'], $variables);
                if ( is_array($widget) && isset($widget['errors']))
                {
                    $success = false;
                }
                else
                {
                    $dashboard['widgets'][$widgetArrayId] = $widget->toArray(true);
                       // $this->Widget->updateWidget($dashboard['widgets'][$widgetArrayId], );
                    $this->Dashboard->save($dashboard);

                    $this->set('widget', $widget->toArray(false));
                    $serialize[] = 'widget';

                    $success= true;
                }
            }
        }

        if ($this->request->is('get')) { //read widget
            $widgetArrayId = $this->Dashboard->findWidgetArrayId($dashboard, $this->request->params['widget_id']);
            $widget = $this->_createWidgetObject($dashboard['widgets'][$widgetArrayId], $variables);
            $this->set('widget', $widget->toArray(false));
            $success= true;
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
     *              type="integer",
     *              required="true",
     *              description="Dashboard ID"
     *          ),
     *          @SWG\Parameter(
     *              name="widget_id",
     *              paramType="path",
     *              type="integer",
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
        $widgets = array();
        foreach($dashboard['Dashboard']['widgets'] as $key => $widget) {
            if ($dashboard['Dashboard']['widgets'][$key]['_id'] != $widgetId) {
                $widgets[] = $dashboard['Dashboard']['widgets'][$key];
            }
        }
        $dashboard['Dashboard']['widgets'] = $widgets;

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
     *              type="integer",
     *              required="true",
     *              description="Dashboard ID"
     *          )
     *      )
     * )
     */
    public function exportDashboard($dashboardId)
    {
        set_time_limit(60*10);  // 10 mins

        $variables = array();

        if (isset($this->request->query['variableStart']) && isset($this->request->query['variableEnd']))
        {
            $variables['start'] =  date('r',$this->request->query['variableStart']);
            $variables['end'] = date('r',$this->request->query['variableEnd']);
        }

        $dashboard = $this->Dashboard->findById($dashboardId);
        $reportName = $dashboard['Dashboard']['name'] . '_' . date('Ymd_Hi') . '.docx';
        $dashboard =  $this->_getParsedDashboard($dashboard['Dashboard'], true, true, $variables);

        $clients = $this->getClientListForUser();
        $clientDetails = $this->Client->find('all', array(
            'conditions' => array('_id' => array('$in' => $clients))
        ));

        $reportPath = $this->Dashboard->generateReport($dashboard, $clientDetails, $reportName);

        $this->response->file($reportPath, array(
            'download' => true,
            'name' => $reportName
        ));
        return $this->response;
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
        if(isset($this->request->params['dashboard_id'])) {
            $dashboardId = $this->request->params['dashboard_id'];
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

        if (!$found) {
            $user['User']['favouriteDashboards'][] = $dashboardId;
        }

        $this->User->save($user['User']);

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
     *              type="string",
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
    private function _createWidgetObject($widget, $variables = array(), $populate = true, $populateLists = false) {
        $widgetObject = WidgetFactory::createWidget($widget, $variables);
        //set the id if we have it otherwise it will be random
        if ( isset($widget['_id']) )
        {
            $widgetObject->setId(new MongoId($widget['_id']));
        }

        //populate the options available for this client on this widget
        $options = $widgetObject->getOptions();

        //this gets populated in _populateOptions
        $mongoPipeLine = array();

        //populate any options that are available
        if ( $populate )
        {
            foreach($options as $optionName => $value) {
                $widgetObject = $this->_populateOptions($options, $optionName, $widgetObject, $mongoPipeLine);
            }

            //dont populate list widgets they will make their own call to search controller (unless this is for a report export)
            if (!($widgetObject instanceof ListWidget) || $populateLists)
            {
                $this->_populateSeries($widgetObject, $mongoPipeLine);
            }
        }

        return $widgetObject;
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

        $clients = $this->getClientListForUser();

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
                if ($option['fieldType'] instanceof FieldTypeAbstract)
                {
                    //find all fields for all clients of this type
                    foreach($clients as $clientId) {
                        $client = $this->Client->findById($clientId);
                        //find the format of this type
                        foreach($client['Client']['fields'] as $format) {
                            //find the format and add to options used for dropdowns
                            if ($format['type'] == $option['fieldType']->getProperties('alias')) {
                                $xOptions[$format['name']] = $option['fieldType']->listDetails($format['name']);
                            }

                            //find the type the widget is using, loginfo is a special case since it holds many types of data
                            if ( $xName == $format['name'] )
                            {
                                $aggregationDetails = $option['fieldType']->getProperties('aggregationDetails');
                                $mongoPipeLine[$optionName][$format['name']] = $aggregationDetails[$xOperation];
                            }
                        }
                    }
                }
                else
                {
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
                                    'isTopLevel' => true,
                                    'groupBy' => '$sum',
                                    'aggregate' => true,
                                );
                            }
                            break;
                        case 'created':
                        case 'modified':
                            $operations = array(
                                'hour' => array(
                                    'dataLocation' => $option['fieldType'],
                                    'isTopLevel' => true,
                                    'groupBy' => array(
                                        'hour' => '$hour',
                                    ),
                                    'aggregate' => false,
                                ),
                                'day' => array(
                                    'dataLocation' => $option['fieldType'],
                                    'isTopLevel' => true,
                                    'groupBy' => array(
                                        'month' => '$month',
                                        'day' => '$dayOfMonth',
                                    ),
                                    'aggregate' => false,
                                ),
                                'month' => array(
                                    'dataLocation' => $option['fieldType'],
                                    'isTopLevel' => true,
                                    'groupBy' => array(
                                        'year' => '$year',
                                        'month' => '$month',
                                    ),
                                    'aggregate' => false,
                                ),
                            );


                            if ($xName == 'created' || $xName == 'modified')
                            {
                                $mongoPipeLine[$optionName][$xName] = $operations[$xOperation];
                            }

                            $xOptions[$option['fieldType']] = array(
                                array(
                                    'name' => $option['fieldType'] . ' By Hour',
                                    'id' => $option['fieldType'] . ':hour',
                                ),
                                array(
                                    'name' => $option['fieldType'] . ' By Day',
                                    'id' => $option['fieldType'] . ':day',
                                ),
                                array(
                                    'name' => $option['fieldType'] . ' By Month',
                                    'id' => $option['fieldType'] . ':month',
                                ),
                            );
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
                                    'isTopLevel' => true,
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
        $query = $widgetObject->getDetail('parsedQuery');
        if (empty($query)) {
            return $widgetObject;
        }

        $clients = $this->getClientListForUser();
        $allClients = $this->Client->find('all', array(
            'conditions' => array('_id' => array('$in' => $clients))
        ));

        $fieldNames = array();
        //add the fields that are being used for grouping (aggregation)
        foreach($aggregationPipeLine as $fieldName => $fields) {
            foreach($fields as $name => $value) {
                $fieldNames[] = $name;
            }
        }

        //get the id's for the fields from each available client
        $fields = array();
        $fullClients = array();
        foreach ($clients as $clientId) {
            $client = $this->Client->findById($clientId);
            $fullClients[] = $this->Client->afterFind($client);
            foreach($client['Client']['fields'] as $format) {
                //does this client have a field with this name?
                if ( in_array($format['name'], $fieldNames) ) {
                    if (!isset($fields[$format['name']])) {
                        $fields[$format['name']] = array();
                    }
                    //add the id for this clients version of the field.
                    $fields[$format['name']][] =  new MongoId($format['_id']);
                }

                if ($format['name'] == 'loginfo'
                        && (in_array('created', $fieldNames) || in_array('modified', $fieldNames))
                    )
                    {
                        if (!isset($fields[$format['name']])) {
                            $fields['loginfo'] = array();
                        }
                        $fields['loginfo'][] = new MongoId($format['_id']);
                }
            }
        }

        $result = array(
            'ok' => 0,
        );

        //send to database and get results.
        if ($widgetObject->isAggregate()) {
           $result = $this->Log->findAggregate($query, $fullClients, $aggregationPipeLine, $fields);
        } else {
            $result = $this->Log->findByQuery($query, $fullClients, $widgetObject->getDetail('orderBy'));
        }

        if ( isset($result['ok']) && !$result['ok'] )
        {
            return array(
                'query' => $query,
                'errors' => $result['errors'],
            );
        }


        if (isset($result['ok'])) {

            $parsedResult = array();

            //remove any mongo'ids from series to show field value
            $options = $widgetObject->getOptions();
            if ( isset($options['series']) )
            {
                $seriesTypeDetails = explode(':', $widgetObject->getDetail('series'));
                $seriesType = $seriesTypeDetails[0];
                $dataLocation = '';
                foreach($options['series'] as $option) {
                    $fieldType = $option['fieldType'];
                    if ($fieldType instanceof FieldTypeAbstract) {
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

                //parse the output and make sure we show correct data
                if ($seriesType instanceof FieldTypeAbstract) {
                    $parsedPoint = array();
                    foreach($result['result'] as $point) {
                        $parsedPoint = $point;
                        if ($point['series'][$dataLocation] instanceof MongoId)
                        {
                            foreach($allClients as $client)
                            {
                                foreach($client['Client']['fields'] as $format)
                                {

                                    //it is some kind of select so search through the options for the value
                                    if (isset($format['data']) && isset($format['data']['options']))
                                    {
                                        foreach($format['data']['options'] as $option)
                                        {
                                            if ($option['_id'] == $point['series'][$dataLocation])
                                            {
                                                $parsedPoint['series'] = $option['name'];
                                            }
                                        }
                                    }
                                    else if ($format['_id'] == $point['series'])
                                    { //client
                                        $parsedPoint['series'] = $client['Client']['name'];
                                    }
                                }
                            }
                        }
                        else
                        {
                            $parsedPoint['series'] = $point['series'][$dataLocation];
                        }
                        $parsedResult[] = $parsedPoint;
                    }

                } else {
                    switch ($seriesType) {
                        case 'client':
                            foreach($result['result'] as $point) {
                                if ($point['series'] instanceof MongoId) {
                                    $client = $this->Client->getClientEntityById( (string) $point['series'] );
                                    $point['series'] = $client->data['name'];
                                }
                                $parsedResult[] = $point;
                            }

                            break;
                    }
                }

                $widgetObject->setSeries($parsedResult);
            }
            else
            {
                $widgetObject->setSeries($result['result']);
            }

            //add widget specific values
            if ( $widgetObject instanceof BenchmarkWidget )
            {
                $widgetObject->setClients( $fullClients );
            }


        } else {
            $widgetObject->setSeries( $result );
        }

        return $widgetObject;
    }

    /**
     * get the list of clients the logged in user can access
     * @return mixed
     */
    private function getClientListForUser() {
        $user = $this->User->findById(
            $this->PreslogAuth->user('_id')
        );

        if ( empty($user) )
        {
            return array();
        }

        $clients = $this->User->listAvailableClientsForUser($user['User']);

        $clientIds = array();
        foreach($clients as $client) {
            $clientIds[] = $client['_id'];
        }
        return $clientIds;
    }
}
