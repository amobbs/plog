<?php

/**
 * Class DashboardController
 */

use Swagger\Annotations as SWG;

class DashboardsController extends AppController
{
    public $uses = array('User', 'Dashboard');


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
        foreach($user['User']['dashboards'] as $dashboardId) {
            $dashboard = $this->Dashboard->findById(new MongoId($dashboardId));
            $fav[] = array(
                'id' => $dashboard['Dashboard']['id'],
                'name' => $dashboard['Dashboard']['name'],
            );
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
        $id = $this->request->params['pass'];
        if ($this->request->is('get')) {
            $dashboard = $this->Dashboard->findById($id[0]);

//            for($i = 0; $i < sizeof($dashboard['Dashboard']['widgets']); $i++) {
//                $dashboard['Dashboard']['widgets'][$i]['highcharts'] = json_decode($dashboard['Dashboard']['widgets'][$i]['highcharts']);
//            }

            $this->set('dashboard', $dashboard['Dashboard']);
            $this->set('status', 'success');
        } else if ($this->request->is('post')) {
            if (!empty($id)) {
                $this->Dashboard->save($this->request->data);
                $this->set('dashboard', $this->request->data);
                $this->set('status', 'saved');
            } else {
                $dashboard = array(
                    '_id' => new MongoId(),
                    'name' => $this->request->data['name'],
                    'type' => 'static',
                    'widgets' => array(
                        array(
                            '_id' => new MongoId(),
                            'name' => 'widget 2',
                            'type' => 'bar',
                            'order' => 0,
                            'highcharts' => $this->Dashboard->serializeDashboardForHighcharts(),
                        )
                    ),
                    'shares' => array(),
                );

                for($i = 0; $i < sizeof($dashboard['widgets']); $i++) {
                    $dashboard['widgets'][$i]['highcharts'] = json_decode($dashboard['widgets'][$i]['highcharts']);
                }

                $this->Dashboard->create($dashboard);
                $this->Dashboard->save();

                $this->set('dashboard',$dashboard);
                $this->set('status', 'created');
            }
        }

        $this->set('favourites', $this->listLoggedInFavouriteDashboards());
        $this->set('_serialize', array('status', 'dashboard', 'favourites'));
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
        $this->Dashboard->delete($dashboardId);

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
        // TODO
        $this->set('todo', 'Edit Widget');
        $this->set('_serialize', array('todo'));
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
    public function deleteWidget()
    {
        // TODO
        $this->set('todo', 'Delete widget');
        $this->set('_serialize', array('todo'));
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
