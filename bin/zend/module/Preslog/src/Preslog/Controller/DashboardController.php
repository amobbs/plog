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

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Swagger\Annotations as SWG;

class DashboardController extends AbstractActionController
{
    /**
     * Fetch the list of dashboards for this users menu
     * @return JsonModel
     */
    public function getDashboardListAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - get dashboard list',
        ));
    }

    /**
     * Create a new dashboard
     * @return JsonModel
     */
    public function createDashboardAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - create new dashboard',
        ));
    }


    /**
     * Fetch the specific dashboard by ID
     * @return JsonModel
     */
    public function readDashboardAction()
    {
        $id = $this->params('dashboard_id');

        return new JsonModel(array(
            'todo' => 'TODO - read specific dashboard ('.$id.')',
        ));
    }


    /**
     * Update specific dashboard by specified ID
     * @return JsonModel
     */
    public function updateDashboardAction()
    {
        $id = $this->params('dashboard_id');

        return new JsonModel(array(
            'todo' => 'TODO - update specific dashboard ('.$id.')',
        ));
    }


    /**
     * Delete the specific dashboard by ID
     * @return JsonModel
     */
    public function deleteDashboardAction()
    {
        $id = $this->params('dashboard_id');

        return new JsonModel(array(
            'todo' => 'TODO - delete specific dashboard ('.$id.')',
        ));
    }


    /**
     * Fetch the list of available dashboard widgets
     * @return JsonModel
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
     */
    public function exportDashboardAsReportAction()
    {
        $dashboard_id = $this->params('id');

        return new ViewModel();
    }

}
