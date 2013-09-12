<?php

/**
 * Class ClientsController
 */

use Swagger\Annotations as SWG;

class ClientsController extends AppController
{
    public $uses = array();


    /**
     * List clients
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.clients.read",
     *      summary="List clients",
     *      notes="User must be an Administrator"
     * )
     */
    public function adminList()
    {
        // TODO
        $this->set('todo', 'Admin List');
        $this->set('_serialize', array('todo'));
    }


    /**
     * Client Options
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.clients.options",
     *      summary="Retrieve form options for clients",
     *      notes="User must be an Administrator"
     * )
     */
    public function adminEditOptions()
    {
        // TODO
        $this->set('todo', 'Admin Edit Options');
        $this->set('_serialize', array('todo'));
    }


    /**
     * Read specified client
     *
     * @SWG\Operation(
     *      partial="admin.clients.specific.read",
     *      summary="Fetch data for a specific client",
     *      notes="User must be an Administrator",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="client_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Client ID"
     *          )
     *      )
     * )
     */
    public function adminRead()
    {
        // TODO
        $this->set('todo', 'Admin Read');
        $this->set('_serialize', array('todo'));
    }


    /**
     * Create a client
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.clients.create",
     *      summary="Create a new client",
     *      notes="User must be an Administrator"
     * )
     *
     * @SWG\Operation(
     *      partial="admin.clients.specific.update",
     *      summary="Update a specific client",
     *      notes="User must be an Administrator",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="client_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Client ID"
     *          )
     *      )
     * )
     */
    public function adminEdit()
    {
        // TODO
        $this->set('todo', 'Admin Edit');
        $this->set('_serialize', array('todo'));
    }


    /**
     * Delete a given client
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.clients.specific.delete",
     *      summary="Delete a specific client",
     *      notes="User must be an Administrator",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="client_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Client ID"
     *          )
     *      )
     * )
     */
    public function adminDelete()
    {
        // TODO
        $this->set('todo', 'Admin Delete');
        $this->set('_serialize', array('todo'));
    }


    /**
     * Duplicate a given client
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.clients.specific.duplicate",
     *      summary="Duplicate a specific client",
     *      notes="User must be an Administrator",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="client_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Client ID"
     *          )
     *      )
     * )
     */
    public function adminDuplicate()
    {
        // TODO
        $this->set('todo', 'Admin Duplicate');
        $this->set('_serialize', array('todo'));
    }

}
