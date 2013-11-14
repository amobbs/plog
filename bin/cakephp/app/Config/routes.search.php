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
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/search',
    array('controller' => 'Search', 'action' => 'search', '[method]' => 'GET')
);

/**
 * Search: GET (fetch logs based on query)
 * @SWG\Resource(
 *      resourcePath="/search",
 *      @SWG\Api(
 *          path="/search/validate",
 *          @SWG\Operation(
 *              @SWG\Partial("search.validate"),
 *              nickname="search.validate",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/search/validate',
    array('controller' => 'Search', 'action' => 'validateQuery', '[method]' => 'GET')
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
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/search/export',
    array('controller' => 'Search', 'action' => 'export', '[method]' => 'GET')
);


/**
 * Search.Wizard.Params: GET (generate params for Query Builder)
 * @SWG\Resource(
 *      resourcePath="/search",
 *      @SWG\Api(
 *          path="/search/wizard/params",
 *          @SWG\Operation(
 *              @SWG\Partial("search.wizard.params"),
 *              nickname="search.wizard.params",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/search/wizard/params',
    array('controller' => 'Search', 'action' => 'wizardParams', '[method]' => 'GET')
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
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/search/wizard/translate',
    array('controller' => 'Search', 'action' => 'wizardTranslate', '[method]' => 'GET')
);

/**
 * Serach.Specific: GET (convert quick search into jql)
 * @SWG\Resource(
 *      resourcePath="/search",
 *      @SWG\Api(
 *          path="/search/wizard/quick",
 *          @SWG\Operation(
 *              @SWG\Partial("search.wizard.quick"),
 *              nickname="search.specific.read",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/search/wizard/quick',
    array('controller' => 'Search', 'action' => 'convertQuickSearchToJql', '[method]' => 'GET')
);