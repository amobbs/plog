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
    '/logs/:log_id',
    array('controller' => 'Logs', 'action' => 'read', '[method]' => 'GET'),
    array('pass'=>array('log_id'), 'log_id'=>'([a-zA-Z]+_[0-9]+)')
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
    '/logs/:log_id',
    array('controller' => 'Logs', 'action' => 'edit', '[method]' => 'POST'),
    array('pass'=>array('log_id'), 'log_id'=>'([a-zA-Z]+_[0-9]+)?')
);
Router::connect(
    '/logs',
    array('controller' => 'Logs', 'action' => 'edit', '[method]' => 'POST')
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
    '/logs/:log_id',
    array('controller' => 'Logs', 'action' => 'delete', '[method]' => 'DELETE'),
    array('pass'=>array('log_id'), 'log_id'=>'([a-zA-Z]+_[0-9]+)')
);


/**
 * Logs: OPTIONS (log opts)
 * @SWG\Resource(
 *      resourcePath="/logs",
 *      @SWG\Api(
 *          path="/logs/{log_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("logs.options"),
 *              nickname="logs.options",
 *              httpMethod="OPTIONS"
 *          )
 *      )
 * )
 */
Router::connect(
    '/logs/:log_id',
    array('controller' => 'Logs', 'action' => 'options', '[method]' => 'OPTIONS'),
    array('pass'=>array('log_id'), 'log_id'=>'([a-zA-Z]+_[0-9]+)?')
);
Router::connect(
    '/logs',
    array('controller' => 'Logs', 'action' => 'options', '[method]' => 'OPTIONS')
);