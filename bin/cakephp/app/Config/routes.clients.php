<?php

/**
 * Clients Routes
 */

use Swagger\Annotations as SWG;


/**
 * Admin.Clients.Read: GET (read list of clients)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/clients",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.clients.read"),
 *              nickname="admin.clients.read",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
	'/admin/clients',
	array('controller' => 'Clients', 'action' => 'adminList', '[method]' => 'GET')
);


/**
 * Admin.Clients.Options: OPTIONS (options for clients)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/clients",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.clients.options"),
 *              nickname="admin.clients.options",
 *              method="OPTIONS"
 *          )
 *      )
 * )
 */
Router::connect(
	'/admin/clients',
	array('controller' => 'Clients', 'action' => 'adminEditOptions', '[method]' => 'OPTIONS')
);


/**
 * Admin.Clients.Create: POST (create a client)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/clients",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.clients.create"),
 *              nickname="admin.clients.create",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
	'/admin/clients',
	array('controller' => 'Clients', 'action' => 'adminEdit', '[method]' => 'POST')
);


/**
 * Admin.Clients.Specific.Read: GET (read a specific client)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/clients/{client_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.clients.specific.read"),
 *              nickname="admin.clients.specific.read",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
	'/admin/clients/:id',
	array('controller' => 'Clients', 'action' => 'adminRead', '[method]' => 'GET'),
	array('pass'=>array('id'), 'id'=>'[0-9a-z]+')
);


/**
 * Admin.Clients.Specific.Update: POST (update a specific client)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/clients/{client_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.clients.specific.update"),
 *              nickname="admin.clients.specific.update",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
	'/admin/clients/:id',
	array('controller' => 'Clients', 'action' => 'adminEdit', '[method]' => 'POST'),
	array('pass'=>array('id'), 'id'=>'[0-9a-z]+')
);


/**
 * Admin.Users.Specific.Delete: DELETE (delete a specific client)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/clients/{client_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.clients.specific.delete"),
 *              nickname="admin.clients.specific.delete",
 *              method="DELETE"
 *          )
 *      )
 * )
 */
Router::connect(
	'/admin/clients/:id',
	array('controller' => 'Clients', 'action' => 'adminDelete', '[method]' => 'DELETE'),
	array('pass'=>array('id'), 'id'=>'[0-9a-z]+')
);


/**
 * Admin.Users.Specific.Duplicate: COPY (duplicate a specific client)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/clients/{client_id}/duplicate",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.clients.specific.duplicate"),
 *              nickname="admin.clients.specific.duplicate",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
	'/admin/clients/:id',
	array('controller' => 'Clients', 'action' => 'adminDuplicate', '[method]' => 'COPY'),
	array('pass'=>array('id'), 'id'=>'[0-9a-z]+')
);


/**
 * Admin.Clients.UploadImage: POST (upload an image for a client)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/clients",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.clients.adminEditPhoto"),
 *              nickname="admin.clients.adminEditPhoto",
 *              method="POST"
 *          )
 *      )
 * )
 */http://local.preslog/admin/clients/52a66adcf3113db81a007d44/photo
Router::connect(
	'/admin/clients/:id/photo',
	array('controller' => 'Clients', 'action' => 'adminEditPhoto', '[method]' => 'POST'),
	array('pass'=>array('id'), 'id'=>'[0-9a-z]+')
);