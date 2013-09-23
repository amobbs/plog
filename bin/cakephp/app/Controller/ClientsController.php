<?php

/**
 * Class ClientsController
 */

use Swagger\Annotations as SWG;

class ClientsController extends AppController
{
    public $uses = array('Client');


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
        // Fetch all clients, with limited fields
        $clients = $this->Client->find('all', array(
            'fields'=>array(
                'id',
                'name',
                'activationDate',
                'created',
                'deleted',
            )
        ));

        // Flatten the array for simplicity
        foreach ($clients as &$client) {
            $client = $client['Client'];
        }

        // TODO: Attach stats to the individual clients
        // TODO: Number of logs / Number of users

        // Output
        $this->set('clients', $clients);
        $this->set('_serialize', array('clients'));
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
    public function adminRead( $id )
    {
        // Fetch user with all fields
        $client = $this->Client->findById( $id );

        // User must exist
        if (!$client)
        {
            $this->errorNotFound(array('message'=>'Client could not be found'));
        }

        // Output
        $this->set($client);
        $this->set('_serialize', array_keys($client));
    }


    /**
     * Create a client
     * @param   id
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
    public function adminEdit( $id=null )
    {
        // Fetch client data
        $client = $this->request->data['Client'];

        // If user has ID, make sure this is the one we save
        if ($id)
        {
            $client['_id'] = $id;
        }

        // Apply data and validate before insert
        $this->Client->set($client);
        if ( !$this->Client->validatesAdminEdit() )
        {
            $this->errorBadRequest( array('data'=>$this->Client->validationErrors, 'message'=>'Validation failed') );
        }

        // Save
        $ret = $this->Client->save( $client );


        // Return success
        $return = array('Success'=>$ret);
        $this->set($return);
        $this->set('_serialize', array_keys($return));
    }


    /**
     * Delete a given client
     * @param   id
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
    public function adminDelete( $id )
    {
        // client must exist
        if (!$this->Client->findById($id)) {
            $this->errorNotFound('Client could not be found');
        }

        // Simple delete save
        $client = array(
            'id'=>$id,
            'deleted'=>true,
        );

        // Delete
        $this->Client->save( array('Client'=>$client) );

        // OK Response
        $this->set('success', true);
        $this->set('_serialize', array('success'));
    }


    /**
     * Duplicate a given client
     * @param   id
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
    public function adminDuplicate( $id )
    {
        // client must exist
        if (!$clientSource = $this->Client->findById($id)) {
            $this->errorNotFound('Client could not be found');
        }

        // Simple copy op
        $client = $clientSource;
        $client['activationDate'] = MongoDate( strtotime('+1 week') );
        $client['name'] = $clientSource['Client']['name'].'_COPY';

        // Delete
        $result = $this->Client->save( array('Client'=>$client) );

        // OK Response
        $this->set('success', $result);
        $this->set('_serialize', array('success'));
    }

}
