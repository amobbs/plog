<?php
/**
 * Preslog Client Controller
 * - Manages clients within the Preslog system
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


class ClientController extends AbstractRestfulController
{

    /**
     * List clients
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.clients.read",
     *      summary="List clients"
     * )
     */
    public function readListAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO: Admin list clients',
        ));
    }


    /**
     * Create a client
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.clients.create",
     *      summary="Create a new client"
     * )
     */
    public function createAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO: Admin create a new client',
        ));
    }


    /**
     * Read a given client
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.clients.specific.read",
     *      summary="Fetch data for a specific client"
     * )
     */
    public function readAction()
    {
        $id = $this->params('id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Admin read specific client ('.$id.')',
        ));
    }


    /**
     * Update a given client
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.clients.specific.update",
     *      summary="Update a specific client"
     * )
     */
    public function updateAction()
    {
        $id = $this->params('id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Admin update specific client ('.$id.')',
        ));
    }


    /**
     * Delete a given client
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.clients.specific.delete",
     *      summary="Delete a specific client"
     * )
     */
    public function deleteAction()
    {
        $id = $this->params('id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Admin delete specific client ('.$id.')',
        ));
    }


    /**
     * Duplicate a given client
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.clients.specific.duplicate",
     *      summary="Duplicate a specific client"
     * )
     */
    public function duplicateAction()
    {
        $id = $this->params('id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Admin duplicate specific client ('.$id.')',
        ));
    }

}
