<?php
/**
 * Preslog Log Controller
 * - Create, Edit, Delete Logs
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

class LogController extends AbstractActionController
{
    /**
     * Prepare fields are data for Log Crate/Edit page
     * Load log from URL param "id" where specified
     * @return JsonModel
     */
    public function readAction()
    {
        $id = $this->params('id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Read Log ('.$id.')',
        ));
    }


    /**
     * Create/Update log
     * Update log by URL param "id" where specified.
     * @return JsonModel
     */
    public function updateAction()
    {
        $id = $this->params('id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Create/Update Log ('.$id.')',
        ));
    }


    /**
     * Delete log by URL param "id"
     * @return JsonModel
     */
    public function deleteAction()
    {
        $id = $this->params('id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Delete Log ('.$id.')',
        ));
    }


}
