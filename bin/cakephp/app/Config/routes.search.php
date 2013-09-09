<?php

/**
 * Search routes
 */

use Swagger\Annotations as SWG;


/**
 * Search: GET (fetch logs based on query)
 * @SWG\Resource(
 *      resourcePath="/search",
 *      @SWG\Api(
 *          path="/search",
 *          @SWG\Operation(
 *              @SWG\Partial("search"),
 *              nickname="search",
 *              httpMethod="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/search',
    array('controller' => 'Search', 'action' => 'search', '[method]' => 'GET')
);


/**
 * Search.Export: GET (fetch and export logs based on query)
 * @SWG\Resource(
 *      resourcePath="/search",
 *      @SWG\Api(
 *          path="/search/export-xls",
 *          @SWG\Operation(
 *              @SWG\Partial("search.export"),
 *              nickname="search.export",
 *              httpMethod="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/search',
    array('controller' => 'Search', 'action' => 'export', '[method]' => 'GET')
);


/**
 * Search.Wizard.Params: GET (generate params for Query Builder)
 * @SWG\Resource(
 *      resourcePath="/search",
 *      @SWG\Api(
 *          path="/search/wizard",
 *          @SWG\Operation(
 *              @SWG\Partial("search.wizard.params"),
 *              nickname="search.wizard.params",
 *              httpMethod="OPTIONS"
 *          )
 *      )
 * )
 */
Router::connect(
    '/search/wizard',
    array('controller' => 'Search', 'action' => 'wizardParams', '[method]' => 'OPTIONS')
);


/**
 * Search.Wizard.Translate: GET (Translate between SQL and JQL)
 * @SWG\Resource(
 *      resourcePath="/search",
 *      @SWG\Api(
 *          path="/search/wizard/translate",
 *          @SWG\Operation(
 *              @SWG\Partial("search.wizard.translate"),
 *              nickname="search.wizard.translate",
 *              httpMethod="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/search/wizard',
    array('controller' => 'Search', 'action' => 'wizardTranslate', '[method]' => 'GET')
);
