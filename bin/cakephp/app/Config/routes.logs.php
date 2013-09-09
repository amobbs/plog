<?php

/**
 * Logs Routes
 */

use Swagger\Annotations as SWG;


/**
 * Logs: GET (read log)
 * @SWG\Resource(
 *      resourcePath="/logs",
 *      @SWG\Api(
 *          path="/logs/{log_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("logs.read"),
 *              nickname="logs.read",
 *              httpMethod="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/logs/:id',
    array('controller' => 'Logs', 'action' => 'edit', '[method]' => 'GET'),
    array('pass'=>array('id'), 'id'=>'[0-9]+')
);


/**
 * Logs: POST (create/update log)
 * @SWG\Resource(
 *      resourcePath="/logs",
 *      @SWG\Api(
 *          path="/logs/{log_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("logs.update"),
 *              nickname="logs.update",
 *              httpMethod="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/logs/:id',
    array('controller' => 'Logs', 'action' => 'edit', '[method]' => 'POST'),
    array('pass'=>array('id'), 'id'=>'[0-9]+')
);


/**
 * Logs: DELETE (delete log)
 * @SWG\Resource(
 *      resourcePath="/logs",
 *      @SWG\Api(
 *          path="/logs/{log_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("logs.delete"),
 *              nickname="logs.delete",
 *              httpMethod="DELETE"
 *          )
 *      )
 * )
 */
Router::connect(
    '/logs/:id',
    array('controller' => 'Logs', 'action' => 'delete', '[method]' => 'DELETE'),
    array('pass'=>array('id'), 'id'=>'[0-9]+')
);


/**
 * Logs: POST (create log)
 * @SWG\Resource(
 *      resourcePath="/logs",
 *      @SWG\Api(
 *          path="/logs/create",
 *          @SWG\Operation(
 *              @SWG\Partial("logs.create"),
 *              nickname="logs.create",
 *              httpMethod="post"
 *          )
 *      )
 * )
 */
Router::connect(
    '/logs',
    array('controller' => 'Logs', 'action' => 'edit', '[method]' => 'POST')
);