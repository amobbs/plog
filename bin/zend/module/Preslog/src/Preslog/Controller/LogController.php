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

use Preslog\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Swagger\Annotations as SWG;

class LogController extends AbstractRestfulController
{
    /**
     * Prepare fields are data for Log Crate/Edit page
     * Load log from URL param "id" where specified
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="logs.read",
     *      summary="Returns a log where requested, and loggable field criteria"
     * )
     */
    public function readAction()
    {
        $id = $this->params('id', 'none specified');

        // :DEBUG: Must be ADMIN for record #1
        if ($id == 401 && !$this->getServiceLocator()->get('rbac')->isGranted('admin'))
        {
            return $this->errorForbidden();
        }
        elseif ($id == 500 && !$this->getServiceLocator()->get('rbac')->isGranted('admin'))
        {
            return $this->errorGeneric(array('message'=>'uh oh'));
        }

        return new JsonModel(array(
            'todo' => 'TODO: Read Log ('.$id.')',
        ));
    }


    /**
     * Create/Update log
     * Update log by URL param "id" where specified.
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="logs.update",
     *      summary="Updates the specified log using POST data"
     * )
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
     *
     * @SWG\Operation(
     *      partial="logs.delete",
     *      summary="Deletes the specified log"
     * )
     */
    public function deleteAction()
    {
        $id = $this->params('id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Delete Log ('.$id.')',
        ));
    }


}
