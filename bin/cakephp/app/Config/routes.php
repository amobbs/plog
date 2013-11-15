<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * PHP 5
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 */


/**
 * Load all routing files
 */
require_once('routes.dashboards.php');
require_once('routes.logs.php');
require_once('routes.users.php');
require_once('routes.clients.php');
require_once('routes.search.php');


/**
 * Swagger-UI Document Interface
 */
Router::connect(
    '/',
    array('controller' => 'Docs', 'action' => 'index')
);


/**
 * Swagger Document Generator
 */
Router::connect(
    '/docs',
    array('controller' => 'Docs', 'action' => 'generateDocumentation')
);

Router::connect(
    '/docs/:resource',
    array('controller' => 'Docs', 'action' => 'generateDocumentation'),
    array('pass'=>array('resource'))
);


/**
 * initial import script
 */
Router::connect(
    '/import',
    array('controller' => 'Import', 'action' => 'runImport')
);

Router::connect(
    '/logs/test/:log_id',
    array('controller' => 'Logs', 'action' => 'notificationtest', '[method]' => 'GET'),
    array('pass'=>array('log_id'), 'log_id'=>'([a-zA-Z]+_[0-9]+)?')
);

/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */

// Disabled because we explicitly define our routes. We don't want Controller/Action URLs here.
//require CAKE . 'Config' . DS . 'routes.php';
