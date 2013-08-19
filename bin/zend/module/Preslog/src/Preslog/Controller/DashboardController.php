<?php
/**
 * Preslog Dashboard Controller
 * - Create, Edit, Delete Dashboards
 * - Add, Edit, Remove Widgets
 * - Render Widgets?
 *
 * @author      4mation Technlogies
 * @link        http://www.4mation.com.au
 * @author      Dave Newson <dave@4mation.com.au>
 * @copyright   Copyright (c) MediaHub Australia
 * @link        http://mediahubaustralia.com.au
 */

namespace Preslog\Controller;

use Preslog\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Swagger\Annotations as SWG;

class DashboardController extends AbstractRestfulController
{
    /**
     * Fetch the list of dashboards for this users menu
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="dashboards.list",
     *      summary="List available dashboards"
     * )
     */
    public function readListAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - get dashboard list',
        ));
    }

    /**
     * Create a new dashboard
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="dashboards.create",
     *      summary="Create a new dashboard"
     * )
     */
    public function createAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - create new dashboard',
        ));
    }


    /**
     * Fetch the specific dashboard by ID
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.read",
     *      summary="Return data for a specific dashboard arrangement"
     * )
     */
    public function readAction()
    {
        $id = $this->params('dashboard_id');

        return new JsonModel(array(
            'todo' => 'TODO - read specific dashboard ('.$id.')',
        ));
    }


    /**
     * Update specific dashboard by specified ID
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.update",
     *      summary="Update the specified dashboard"
     * )
     */
    public function updateAction()
    {
        $id = $this->params('dashboard_id');

        return new JsonModel(array(
            'todo' => 'TODO - update specific dashboard ('.$id.')',
        ));
    }


    /**
     * Delete the specific dashboard by ID
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.delete",
     *      summary="Delete the specified dashboard"
     * )
     */
    public function deleteAction()
    {
        $id = $this->params('dashboard_id');

        return new JsonModel(array(
            'todo' => 'TODO - delete specific dashboard ('.$id.')',
        ));
    }


    /**
     * Fetch the list of available dashboard widgets
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="widgets.list",
     *      summary="List all available widgets"
     * )
     */
    public function readWidgetListAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - read widget list',
        ));
    }


    /**
     * Fetch options for this particular widget type
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="widgets.options",
     *      summary="Fetch options for the specified widget type"
     * )
     */
    public function readWidgetOptionsAction()
    {
        $widget_type = $this->params('widget_type');

        return new JsonModel(array(
            'todo' => 'TODO - read widget type ('.$widget_type.') options',
        ));
    }


    /**
     * Fetch a specific dashboard widget (construct and/or data) for display
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.widgets.specific.read",
     *      summary="Fetch data for a specified widget"
     * )
     */
    public function readDashboardWidgetAction()
    {
        $dashboard_id = $this->params('id');
        $widget_id = $this->params('widget_id');

        return new JsonModel(array(
            'todo' => 'TODO - read widget ('.$widget_id.') from dashboard ('.$dashboard_id.')',
        ));
    }


    /**
     * Create a widget on a dashboard
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.widgets.create",
     *      summary="Create a new widget on the specified dashboard"
     * )
     */
    public function createDashboardWidgetAction()
    {
        $dashboard_id = $this->params('id');

        return new JsonModel(array(
            'todo' => 'TODO - create widget on dashboard ('.$dashboard_id.')',
        ));
    }


    /**
     * Update/edit a widget on a dashboard
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.widgets.specific.update",
     *      summary="Update the specified widget"
     * )
     */
    public function updateDashboardWidgetAction()
    {
        $dashboard_id = $this->params('id');
        $widget_id = $this->params('widget_id');

        return new JsonModel(array(
            'todo' => 'TODO - update widget ('.$widget_id.') on dashboard ('.$dashboard_id.')',
        ));
    }


    /**
     * Delete a widget on a dashboard
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.widgets.specific.delete",
     *      summary="Deletes the specified widget"
     * )
     */
    public function deleteDashboardWidgetAction()
    {
        $dashboard_id = $this->params('id');
        $widget_id = $this->params('widget_id');

        return new JsonModel(array(
            'todo' => 'TODO - delete widget ('.$widget_id.') on dashboard ('.$dashboard_id.')',
        ));
    }


    /**
     * Export the given dashboard widgets source data as an XLS file
     * @return ViewModel
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.widgets.specific.export",
     *      summary="Instigate download of XLS containing logs used to compile specified widget"
     * )
     */
    public function exportDashboardWidgetDataAsXlsAction()
    {
        $dashboard_id = $this->params('id');
        $widget_id = $this->params('widget_id');

        return new ViewModel();
    }


    /**
     * Export the given dashboard as a Word Document report
     * @return ViewModel
     *
     * @SWG\Operation(
     *      partial="dashboards.specific.export",
     *      summary="Instigate download of DOCX containing data from the specified dashboard"
     * )
     */
    public function exportDashboardAsReportAction()
    {
        $dashboard_id = $this->params('id');

        return new ViewModel();
    }

}
