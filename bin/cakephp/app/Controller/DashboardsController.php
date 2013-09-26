<?php


use Swagger\Annotations as SWG;
use Preslog\Widgets\WidgetFactory;

/**
 * Class DashboardController
 */
class DashboardsController extends AppController
{
    public $uses = array('User', 'Dashboard', 'Widget');


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
        $this->set('dashboards', $this->Dashboard->serializeDashboardForHighcharts());
        $this->set('_serialize', array('preset', 'favourites', 'dashboards'));
    }


    /**
     * given the logged in user list their favourite dashbaords
     * @return array
     */

    private function listLoggedInFavouriteDashboards() {
        $user = $this->User->findById(
            $this->PreslogAuth->user('id'),
            array('fields'=>array(
                'dashboards',
            ))
        );

        $fav = array();
        if (isset($user['User']['dashboards'])) {
            foreach($user['User'] as $dashboardId) {
                $dashboard = $this->Dashboard->findById(new MongoId($dashboardId));
                $fav[] = array(
                    'id' => $dashboard['Dashboard']['id'],
                    'name' => $dashboard['Dashboard']['name'],
                );
            }
        }

        return $fav;
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
        $id = isset($this->request->params['pass'][0]) ? $this->request->params['pass'][0] : "";
        if ($this->request->is('get')) { //read dashboard
            $dashboard = $this->Dashboard->findById($id);
            $this->set('dashboard', $this->Dashboard->toArray($dashboard['Dashboard']));
            $this->set('status', 'success');
        } else if ($this->request->is('post')) {
            if (!empty($id)) { //edit dashboard
                if(isset($this->request->data['widgets'])) { //just update the widgets
                    $this->updateDashboardWidgets($id, $this->request->data['widgets']);
                    $dashboard = $this->Dashboard->findById($id);
                } else { //update all the values
                    $dashboard = $this->Dashboard->findById($id);
                    $this->Dashboard->save($this->request->data);
                 //   $dashboard = $this->Dashboard->findById($this->request->data['id']);
                }

                $this->set('dashboard', $this->Dashboard->toArray($dashboard['Dashboard']));
                $this->set('status', 'saved');
            } else { //new dashboard
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
                $this->set('dashboard', $this->Dashboard->toArray($dashboard['Dashboard']));
                $this->set('status', 'created');
            }
        }

        $this->set('favourites', $this->listLoggedInFavouriteDashboards());
        $this->set('_serialize', array('status', 'dashboard', 'favourites'));
    }

    private function updateDashboardWidgets($id, $widgets) {
        $dashboard = array(
            'id' => new MongoId($id),
            'widgets' => array(),
        );
        foreach($widgets as $widget) {
            $widgetObject = WidgetFactory::createWidget($widget);
            $widgetObject->setId(new MongoId($widget['id']));
            $dashboard['widgets'][] = $widgetObject->toArray();
        }

        $this->Dashboard->save($dashboard, false, array(
            'id',
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
            if (!isset( $this->request->params['widget_id'])) { //create widget
                $widget = $this->createWidget($this->request->data['widget']);
                $dashboard['widgets'][] = $widget->toArray();
                $this->Dashboard->save($dashboard);
                $this->set('widget', $widget->toArray());
                $serialize[] = 'widget';

                $success = true;
            } else { //edit widget
                $widgetArrayId = $this->Dashboard->findWidgetArrayId($dashboard, $this->request->params['widget_id']);
                $dashboard['widgets'][$widgetArrayId] = $this->Widget->updateWidget($dashboard['widgets'][$widgetArrayId], $this->request->data['widget']);
                $this->Dashboard->save($dashboard);
                $widget = WidgetFactory::createWidget($dashboard['widgets'][$widgetArrayId]);

                $this->Dashboard->save($dashboard);
                $this->set('widget', $widget->toArray());
                $serialize[] = 'widget';

                $success= true;
            }
        }

        if ($this->request->is('get')) { //read widget
            $widgetArrayId = $this->Dashboard->findWidgetArrayId($dashboard, $this->request->params['widget_id']);
            $widget = WidgetFactory::createWidget($dashboard['widgets'][$widgetArrayId]);
            $this->set('widget', $widget->toArray());
            $serialize[] = 'widget';
        }

        $this->set('success', $success);
        $this->set('_serialize', $serialize);
    }

    private function createWidget($data) {
        if (!isset($data['name'])) $data['name'] = 'Widget';
        $widget = WidgetFactory::createWidget($data);
        $widget->setId(new MongoId());
        return $widget;
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
        foreach($dashboard['Dashboard']['widgets'] as $key => $widget) {
            if ($dashboard['Dashboard']['widgets'][$key]['id'] == $widgetId) {
                unset($dashboard['Dashboard']['widgets'][$key]);
                break;
            }
        }

        $this->Dashboard->save($dashboard);
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
        $reportPath = $this->Dashboard->generateReport($dashboard, $reportName);

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
        // TODO
        $this->set('todo', 'Edit Favourite Dashboards');
        $this->set('_serialize', array('todo'));
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


}
