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
 *              httpMethod="GET"
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
 *              httpMethod="OPTIONS"
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
 *              httpMethod="POST"
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
 *              httpMethod="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/admin/clients/:id',
    array('controller' => 'Clients', 'action' => 'adminEdit', '[method]' => 'GET'),
    array('pass'=>array('id'), 'id'=>'[0-9]+')
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
 *              httpMethod="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/admin/clients/:id',
    array('controller' => 'Clients', 'action' => 'adminEdit', '[method]' => 'POST'),
    array('pass'=>array('id'), 'id'=>'[0-9]+')
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
 *              httpMethod="DELETE"
 *          )
 *      )
 * )
 */
Router::connect(
    '/admin/clients/:id',
    array('controller' => 'Clients', 'action' => 'adminDelete', '[method]' => 'DETELE'),
    array('pass'=>array('id'), 'id'=>'[0-9]+')
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
 *              httpMethod="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/admin/clients/:id',
    array('controller' => 'Clients', 'action' => 'adminDuplicate', '[method]' => 'POST'),
    array('pass'=>array('id'), 'id'=>'[0-9]+')
);

