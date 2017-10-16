<?php

/**
 * Class ClientsController
 */

use Swagger\Annotations as SWG;

/**
 * Class ClientsController
 * @property    Log     Log
 * @property    Client  Client
 */
class ClientsController extends AppController
{
	public $uses = array('Client', 'Log', 'User');


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
				'_id',
				'name',
				'logoUrl',
				'activationDate',
				'benchmark',
				'created',
				'deleted',
			)
		));

		// Flatten the array for simplicity
		foreach ($clients as &$client) {

			// Truncate the structure a little
			$client = $client['Client'];

			// Get the Logo
			$client['logo'] = $this->Client->getLogoPath($client);

			// Get the number of logs
			$client['stats']['numLogs'] = $this->Log->find('count', array(
				'conditions'=>array(
					'client_id' => new MongoId($client['_id'])
				)
			));

			// Get the number of users
			$client['stats']['numUsers'] = $this->User->find('count', array(
				'conditions'=>array(
					'client_id' => new MongoId($client['_id'])
				)
			));

			// Get the number of services (networks)
			$client['stats']['numServices'] = count($this->Client->getActiveServices(new MongoId($client['_id'])));

		}

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
		// Fetch list of field types
		$return['fieldTypes'] = $this->Log->getFieldTypes();

		// return data
		$this->set($return);
		$this->set('_serialize', array_keys($return));
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
	 *              type="integer",
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

		// Get the number of users
		$client['Client']['stats']['numServices'] = count($this->Client->getActiveServices($id));

		// User must exist
		if (!$client)
		{
			$this->errorNotFound(array('message'=>'Client could not be found'));
		}

		// Get Logo
		$client['Client']['logo-img'] = $this->Client->getLogoPath($client['Client']);
		$client['Client']['logoImg'] = $this->Client->getLogoPath($client['Client']);
		unset($client['Client']['logoUrl']);

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
	 *              type="integer",
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


	public function adminEditPhoto($id) {
		// Fetch user with all fields
		$client = $this->Client->findById( $id );

		// User must exist
		if (!$client)
		{
			$this->errorNotFound(array('message'=>'Client could not be found'));
		}

		$projectRoot = dirname(dirname(APP));
		$webRoot = $projectRoot . '/webroot/';
		$uploadDir = $webRoot . '/uploads/';
		if (!file_exists($uploadDir)) {
			mkdir($uploadDir, 777, true);
		}

		// delete old file if it exists
		$existingFile = $webRoot . $client['Client']['logoUrl'];
		if (file_exists($existingFile)) {
			unlink($existingFile);
		}

		$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		// we add a unique string to each file to avoid caching of old images
		$cacheKey = substr(md5(time()), 0, 5);
		$uploadedFileName = $id . '-' . $cacheKey . '.' . $ext;

		if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $uploadedFileName)) {
			$return = array('Success'=> false);
			$this->set($return);
			$this->set('_serialize', array_keys($return));
			return;
		}

		$client['Client']['logoUrl'] = '/uploads/' . $uploadedFileName;
		$this->Client->set($client);
		$ret = $this->Client->save( $client );

		$return = array('Success' => $ret);
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
	 *              type="integer",
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

		// Delete
		$this->Client->save(
			array(
				'_id'=>$id,
				'deleted'=>true,
			),
			false,
			array(
				'deleted',
			)
		);

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
	 *              type="integer",
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
