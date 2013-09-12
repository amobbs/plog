<?php

/**
 * Class DashboardController
 */

use Swagger\Annotations as SWG;

class DashboardsController extends AppController
{
    public $uses = array();


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
        // TODO
        $this->set('todo', 'List Dashboards');
        $this->set('data', $this->Dashboard->serializeDashboardForHighcharts());
        $this->set('_serialize', array('todo', 'data'));
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
        // TODO
        $this->set('todo', 'Edit Dashboard');
        $this->set('_serialize', array('todo'));
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
    public function deleteDashboard()
    {
        // TODO
        $this->set('todo', 'Delete Dashboard');
        $this->set('_serialize', array('todo'));
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
    public function exportDashboard()
    {

        $reportName = 'example_report.docx';
        $reportPath = $this->Dashboard->generateReport($reportName);

        $this->response->file($reportPath, array(
            'download' => true,
            'name' => $reportName
        ));
        //Return reponse object to prevent controller from trying to render a view
        return $this->response;

//        $file = $this->Attachment->getFile(TMP . $reportName);
//        $this->response->file($file['path']);
//        //Return reponse object to prevent controller from trying to render a view
//        $this->response->file($file['path'], array('download' => true, 'name' => $reportName));
//        return $this->response;

        // TODO
//        $this->set('todo', 'Export Dashboard');
//        $this->set('_serialize', array('todo'));
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
        // TODO
        $this->set('todo', 'List Favourite Dashboards');
        $this->set('_serialize', array('todo'));
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
