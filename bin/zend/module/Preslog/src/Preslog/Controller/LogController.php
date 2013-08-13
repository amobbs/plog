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
     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/logs",
     *          @SWG\Operation(
     *              nickname="index",
     *              summary="Read fields and log where specified",
     *              httpMethod="GET",
     *              responseClass="User"
     *          )
     *      )
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
     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/logs",
     *          @SWG\Operation(
     *              nickname="index",
     *              summary="Update or create a log",
     *              httpMethod="POST",
     *              responseClass="User"
     *          )
     *      )
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
     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/logs",
     *          @SWG\Operation(
     *              nickname="index",
     *              summary="Remove a log",
     *              httpMethod="DELETE",
     *              responseClass="User"
     *          )
     *      )
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
