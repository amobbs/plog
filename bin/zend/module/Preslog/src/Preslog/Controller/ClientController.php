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
     *      summary="List clients",
     *      notes="User must be an Administrator"
     * )
     */
    public function readListAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO: Admin list clients',
        ));
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
    public function optionsAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO: Admin client options',
        ));
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
    public function readAction()
    {
        $id = $this->params('client_id', 'none specified');

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
    public function updateAction()
    {
        $id = $this->params('client_id', 'none specified');

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
    public function deleteAction()
    {
        $id = $this->params('client_id', 'none specified');

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
    public function duplicateAction()
    {
        $id = $this->params('client_id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Admin duplicate specific client ('.$id.')',
        ));
    }

}
