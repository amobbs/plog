<?php
/**
 * Preslog Module Config
 * - Maps routes to controllers
 * - Set Service availability
 * - Map controllers
 * - Manage views
 *
 * @author      4mation Technlogies
 * @link        http://www.4mation.com.au
 * @author      Dave Newson <dave@4mation.com.au>
 * @copyright   Copyright (c) MediaHub Australia
 * @link        http://mediahubaustralia.com.au
 */

use Swagger\Annotations as SWG;

return array(

    /**
     * Routes
     * WARNING: If you update the routes below, update the SWG annotations!
     * Documentation follows formatting of swagger-php
     *   http://zircote.com/swagger-php/
     *   https://github.com/wordnik/swagger-core/wiki
     */

    'router' => array(
        'routes' => array(

            // Homepage API Explorer
            'api' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'may_terminate' => true,
                    'defaults' => array(
                        'controller' => 'Preslog\Controller\Api',
                        'action'     => 'swaggerView',
                    ),
                ),
            ),

            // Homepage API Documentation
            'api.docs' => array(
                'type' => 'Zend\Mvc\Router\Http\Regex',
                'options' => array(
                    'regex' => '/docs(?<resource>.*)',
                    'spec'  => '/docs%resource%',
                    'may_terminate' => true,
                    'defaults' => array(
                        'controller' => 'Preslog\Controller\Api',
                        'action'     => 'swaggerDocs',
                    ),
                ),
            ),

            // Users: My-Profile
            'users.my-profile' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/users/my-profile',
                ),

                'child_routes' => array(

                    /**
                     * Users.My-Profile: GET (read user)
                     * @SWG\Resource(
                     *      resourcePath="/users",
                     *      @SWG\Api(
                     *          path="/users/my-profile",
                     *          @SWG\Operation(
                     *              nickname="users.my-profile.read",
                     *              httpMethod="GET"
                     *          )
                     *      )
                     * )
                     */
                    'users.my-profile.read' => array(
                        'type' => 'Zend\Mvc\Router\Http\Method',
                        'may_terminate' => true,
                        'options' => array(
                            'verb' => 'get',
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\User',
                                'action' => 'readMyProfile'
                            ),
                        ),
                    ),

                    /**
                     * Users.My-Profile: POST (update user)
                     * @SWG\Resource(
                     *      resourcePath="/users",
                     *      @SWG\Api(
                     *          path="/users/my-profile",
                     *          @SWG\Operation(
                     *              nickname="users.my-profile.update",
                     *              httpMethod="POST"
                     *          )
                     *      )
                     * )
                     */
                    'users.my-profile.update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Method',
                        'may_terminate' => true,
                        'options' => array(
                            'verb' => 'post,put',
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\User',
                                'action' => 'updateMyProfile'
                            ),
                        ),
                    ),
                ),
            ),

            // Users.My-Notifications
            'users.my-notifications' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/users/my-notifications',
                ),
                'child_routes' => array(

                    /**
                     * User.My-Notifications: GET (read notifications)
                     * @SWG\Resource(
                     *      resourcePath="/users",
                     *      @SWG\Api(
                     *          path="/users/my-notifications",
                     *          @SWG\Operation(
                     *              nickname="users.my-notifications.read",
                     *              httpMethod="GET"
                     *          )
                     *      )
                     * )
                     */
                    'users.my-notifications.read' => array(
                        'type' => 'Zend\Mvc\Router\Http\Method',
                        'may_terminate' => true,
                        'options' => array(
                            'verb' => 'get',
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\User',
                                'action' => 'readMyNotifications'
                            ),
                        ),
                    ),

                    /**
                     * User.My-Notifications: POST (update notifications)
                     * @SWG\Resource(
                     *      resourcePath="/users",
                     *      @SWG\Api(
                     *          path="/users/my-notifications",
                     *          @SWG\Operation(
                     *              nickname="users.my-notifications.update",
                     *              httpMethod="POST"
                     *          )
                     *      )
                     * )
                     */
                    'users.my-notifications.update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Method',
                        'may_terminate' => true,
                        'options' => array(
                            'verb' => 'post',
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\User',
                                'action' => 'updateMyNotifications'
                            ),
                        ),
                    ),

                ),
            ),


            // Logs
            'logs' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/logs[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                ),
                'child_routes' => array(

                    /**
                     * Logs: GET (read log)
                     * @SWG\Resource(
                     *      resourcePath="/logs",
                     *      @SWG\Api(
                     *          path="/logs/{logId}",
                     *          @SWG\Operation(
                     *              nickname="logs.read",
                     *              httpMethod="GET"
                     *          )
                     *      )
                     * )
                     */
                    'logs.read' => array(
                        'type' => 'Zend\Mvc\Router\Http\Method',
                        'options' => array(
                            'verb' => 'get',
                            'may_terminate' => true,
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\Log',
                                'action' => 'read'
                            ),
                        ),
                    ),

                    /**
                     * Logs: POST (create/update log)
                     * @SWG\Resource(
                     *      resourcePath="/logs",
                     *      @SWG\Api(
                     *          path="/logs/{logId}",
                     *          @SWG\Operation(
                     *              nickname="logs.update",
                     *              httpMethod="POST"
                     *          )
                     *      )
                     * )
                     */
                    'logs.update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Method',
                        'may_terminate' => true,
                        'options' => array(
                            'verb' => 'post,put',
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\Log',
                                'action' => 'update'
                            ),
                        ),
                    ),

                    /**
                     * Logs: DELETE (delete log)
                     * @SWG\Resource(
                     *      resourcePath="/logs",
                     *      @SWG\Api(
                     *          path="/logs/{logId}",
                     *          @SWG\Operation(
                     *              nickname="logs.delete",
                     *              httpMethod="DELETE"
                     *          )
                     *      )
                     * )
                     */
                    'logs.delete' => array(
                        'type' => 'Zend\Mvc\Router\Http\Method',
                        'may_terminate' => true,
                        'options' => array(
                            'verb' => 'delete',
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\Log',
                                'action' => 'delete'
                            ),
                        ),
                    ),
                ),
            ),


            // Dashboards
            'dashboards' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/dashboards',
                ),
                'child_routes' => array(

                    /**
                     * Dashboards: GET (read dashboards)
                     * @SWG\Resource(
                     *      resourcePath="/dashboards",
                     *      @SWG\Api(
                     *          path="/dashboards",
                     *          @SWG\Operation(
                     *              nickname="dashboards.list",
                     *              httpMethod="GET"
                     *          )
                     *      )
                     * )
                     */
                    'dashboards.read' => array(
                        'type' => 'Zend\Mvc\Router\Http\Method',
                        'may_terminate' => true,
                        'options' => array(
                            'verb' => 'get',
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\Dashboard',
                                'action' => 'readList'
                            ),
                        ),
                    ),

                    // Dashboards.Specific
                    'dashboards.specific' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/:dashboard_id',
                            'constraints' => array(
                                'dashboard_id' => '[0-9]+'
                            ),
                        ),
                        'child_routes' => array(

                            /**
                             * Dashboards.Specific: GET (read dashboard)
                             * @SWG\Resource(
                             *      resourcePath="/dashboards",
                             *      @SWG\Api(
                             *          path="/dashboards/{dashboardId}",
                             *          @SWG\Operation(
                             *              nickname="dashboards.specific.read",
                             *              httpMethod="GET"
                             *          )
                             *      )
                             * )
                             */
                            'dashboards.specific.read' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'get',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Dashboard',
                                        'action' => 'read'
                                    ),
                                ),
                            ),

                            /**
                             * Dashboards.Specific: POST (update dashboard)
                             * @SWG\Resource(
                             *      resourcePath="/dashboards",
                             *      @SWG\Api(
                             *          path="/dashboards/{dashboardId}",
                             *          @SWG\Operation(
                             *              nickname="dashboards.specific.update",
                             *              httpMethod="POST"
                             *          )
                             *      )
                             * )
                             */
                            'dashboards.specific.update' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'post,put',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Dashboard',
                                        'action' => 'update'
                                    ),
                                ),
                            ),

                            /**
                             * Dashboards.Specific: DELETE (delete dashboard)
                             * @SWG\Resource(
                             *      resourcePath="/dashboards",
                             *      @SWG\Api(
                             *          path="/dashboards/{dashboardId}",
                             *          @SWG\Operation(
                             *              nickname="dashboards.specific.delete",
                             *              httpMethod="DELETE"
                             *          )
                             *      )
                             * )
                             */
                            'dashboards.specific.delete' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'delete',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Dashboard',
                                        'action' => 'delete'
                                    ),
                                ),
                            ),

                            // Dashboard.Specific.Widgets
                            'dashboards.specific.widgets' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => array(
                                    'route' => '/widgets',
                                ),
                                'child_routes' => array(

                                    /**
                                     * Dashboard.Specific.Widgets.Create: POST (create widget on dashboard)
                                     * @SWG\Resource(
                                     *      resourcePath="/dashboards",
                                     *      @SWG\Api(
                                     *          path="/dashboards/{dashboardId}/widgets",
                                     *          @SWG\Operation(
                                     *              nickname="dashboards.specific.widgets.create",
                                     *              httpMethod="POST"
                                     *          )
                                     *      )
                                     * )
                                     */
                                    'dashboards.specific.widgets.create' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'post,put',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\Dashboard',
                                                'action' => 'createDashboardWidget'
                                            ),
                                        ),
                                    ),

                                    // Dashboard.Specific.Widgets.Specific
                                    'dashboards.specific.widgets.specific' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => array(
                                            'route' => '/:widget_id',
                                            'constraints' => array(
                                                'widget_id' => '[0-9]+'
                                            ),
                                        ),
                                        'child_routes' => array(

                                            /**
                                             * Dashboards.Specific.Widgets.Specific.Read: GET (read existing widget)
                                             * @SWG\Resource(
                                             *      resourcePath="/dashboards",
                                             *      @SWG\Api(
                                             *          path="/dashboards/{dashboardId}/widgets/{widgetId}",
                                             *          @SWG\Operation(
                                             *              nickname="dashboards.specific.widgets.specific.read",
                                             *              httpMethod="GET"
                                             *          )
                                             *      )
                                             * )
                                             */
                                            'dashboards.specific.widgets.specific.read' => array(
                                                'type' => 'Zend\Mvc\Router\Http\Method',
                                                'may_terminate' => true,
                                                'options' => array(
                                                    'verb' => 'get',
                                                    'defaults' => array(
                                                        'controller' => 'Preslog\Controller\Dashboard',
                                                        'action' => 'readDashboardWidget'
                                                    ),
                                                ),
                                            ),

                                            /**
                                             * Dashboards.Specific.Widgets.Specific.Update: POST (update existing widget)
                                             * @SWG\Resource(
                                             *      resourcePath="/dashboards",
                                             *      @SWG\Api(
                                             *          path="/dashboards/{dashboardId}/widgets/{widgetId}",
                                             *          @SWG\Operation(
                                             *              nickname="dashboards.specific.widgets.specific.update",
                                             *              httpMethod="POST"
                                             *          )
                                             *      )
                                             * )
                                             */
                                            'dashboards.specific.widgets.specific.update' => array(
                                                'type' => 'Zend\Mvc\Router\Http\Method',
                                                'may_terminate' => true,
                                                'options' => array(
                                                    'verb' => 'post,put',
                                                    'defaults' => array(
                                                        'controller' => 'Preslog\Controller\Dashboard',
                                                        'action' => 'updateDashboardWidget'
                                                    ),
                                                ),
                                            ),

                                            /**
                                             * Dashboards.Specific.Widgets.Specific.Delete: DELETE (remove existing widget)
                                             * @SWG\Resource(
                                             *      resourcePath="/dashboards",
                                             *      @SWG\Api(
                                             *          path="/dashboards/{dashboardId}/widgets/{widgetId}",
                                             *          @SWG\Operation(
                                             *              nickname="dashboards.specific.widgets.specific.delete",
                                             *              httpMethod="DELETE"
                                             *          )
                                             *      )
                                             * )
                                             */
                                            'dashboards.specific.widgets.specific.delete' => array(
                                                'type' => 'Zend\Mvc\Router\Http\Method',
                                                'may_terminate' => true,
                                                'options' => array(
                                                    'verb' => 'delete',
                                                    'defaults' => array(
                                                        'controller' => 'Preslog\Controller\Dashboard',
                                                        'action' => 'deleteDashboard'
                                                    ),
                                                ),
                                            ),

                                            /**
                                             * Dashboards.Specific.Widgets.Specific.Export: GET (export widget logdata to xls)
                                             * @SWG\Resource(
                                             *      resourcePath="/dashboards",
                                             *      @SWG\Api(
                                             *          path="/dashboards/{dashboardId}/widgets/{widgetId}/export",
                                             *          @SWG\Operation(
                                             *              nickname="dashboards.specific.widgets.specific.export",
                                             *              httpMethod="GET"
                                             *          )
                                             *      )
                                             * )
                                             */
                                            'dashboards.specific.widgets.specific.export-xls' => array(
                                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                                'options' => array(
                                                    'route' => '/export-xls',
                                                    'defaults' => array(
                                                        'controller' => 'Preslog\Controller\Dashboard',
                                                        'action' => 'exportDashboardWidgetDataAsXls'
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),

                            /**
                             * Dashboards.specific.export-report
                             * @SWG\Resource(
                             *      resourcePath="/dashboards",
                             *      @SWG\Api(
                             *          path="/dashboards/{dashboardId}/export",
                             *          @SWG\Operation(
                             *              nickname="dashboards.specific.export",
                             *              httpMethod="GET"
                             *          )
                             *      )
                             * )
                             */
                            'dashboards.specific.widgets' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => array(
                                    'route' => '/export-report',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Dashboard',
                                        'action' => 'exportDashboardAsDoc'
                                    ),
                                ),
                            ),
                        ),
                    ),

                    //
                    /**
                     * Dashboard.Create: POST (create new dashboard)
                     * @SWG\Resource(
                     *      resourcePath="/dashboards",
                     *      @SWG\Api(
                     *          path="/dashboards",
                     *          @SWG\Operation(
                     *              nickname="dashboards.create",
                     *              httpMethod="POST"
                     *          )
                     *      )
                     * )
                     */
                    'dashboards.create-abstract' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/create',
                        ),
                        'child_routes' => array(
                            'dashboards.create' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'post,put',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Dashboard',
                                        'action' => 'create',
                                    ),
                                ),
                            ),
                        ),
                    ),


                ),
            ),


            /**
             * Widgets
             * @SWG\Resource(
             *      resourcePath="/widgets",
             *      @SWG\Api(
             *          path="/widgets",
             *          @SWG\Operation(
             *              nickname="widgets.list",
             *              httpMethod="GET"
             *          )
             *      )
             * )
             */
            'widgets' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/widgets',
                    'defaults' => array(
                        'controller' => 'Preslog\Controller\Dashboard',
                        'action' => 'readWidgetList',
                    ),
                ),
                'child_routes' => array(

                    /**
                     * Widgets.Options
                     * @SWG\Resource(
                     *      resourcePath="/widgets",
                     *      @SWG\Api(
                     *          path="/widgets/{widgetId}",
                     *          @SWG\Operation(
                     *              nickname="widgets.options",
                     *              httpMethod="GET"
                     *          )
                     *      )
                     * )
                     */
                    'widgets.options' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'may_terminate' => true,
                        'options' => array(
                            'route' => '/:widget_type',
                            'constraints' => array(
                                'widget_type' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\Dashboard',
                                'action' => 'readWidgetOptions',
                            ),
                        ),
                    ),
                ),
            ),


            /**
             * Search: GET (fetch logs based on query)
             * @SWG\Resource(
             *      resourcePath="/search",
             *      @SWG\Api(
             *          path="/search",
             *          @SWG\Operation(
             *              nickname="search",
             *              httpMethod="GET"
             *          )
             *      )
             * )
             */
            'search' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/search',
                    'defaults' => array(
                        'controller' => 'Preslog\Controller\Search',
                        'action' => 'search',
                    ),
                ),
                'child_routes' => array(


                    /**
                     * Search.Export: GET (fetch and export logs based on query)
                     * @SWG\Resource(
                     *      resourcePath="/search",
                     *      @SWG\Api(
                     *          path="/search/export-xls",
                     *          @SWG\Operation(
                     *              nickname="search.export",
                     *              httpMethod="GET"
                     *          )
                     *      )
                     * )
                     */
                    'search.export' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'may_terminate' => true,
                        'options' => array(
                            'route' => '/export-xls',
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\Search',
                                'action' => 'searchExportAsXlsOptions',
                            ),
                        ),
                    ),

                    // Search.Wizard
                    'search.wizard' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/wizard',
                        ),
                        'child_routes' => array(


                            /**
                             * Search.Wizard.Params: GET (generate params for Query Builder)
                             * @SWG\Resource(
                             *      resourcePath="/search",
                             *      @SWG\Api(
                             *          path="/search/wizard/params",
                             *          @SWG\Operation(
                             *              nickname="search.wizard.params",
                             *              httpMethod="GET"
                             *          )
                             *      )
                             * )
                             */
                            'search.wizard.params' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'may_terminate' => true,
                                'options' => array(
                                    'route' => '/params',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Search',
                                        'action' => 'searchWizardParams',
                                    ),
                                ),
                            ),

                            /**
                             * Search.Wizard.Translate: GET (Translate between SQL and JQL)
                             * @SWG\Resource(
                             *      resourcePath="/search",
                             *      @SWG\Api(
                             *          path="/search/wizard/translate",
                             *          @SWG\Operation(
                             *              nickname="search.wizard.translate",
                             *              httpMethod="GET"
                             *          )
                             *      )
                             * )
                             */
                            'search.wizard.translate' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'may_terminate' => true,
                                'options' => array(
                                    'route' => '/translate',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Search',
                                        'action' => 'searchWizardTranslate',
                                    ),
                                ),
                            ),

                        ),
                    ),
                ),
            ),


            // Admin
            'admin' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin',
                ),
                'child_routes' => array(

                    // Admin.Users
                    'admin.users' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/users',
                        ),
                        'child_routes' => array(

                            /**
                             * Admin.Users.Read: GET (read list of users)
                             * @SWG\Resource(
                             *      resourcePath="/admin",
                             *      @SWG\Api(
                             *          path="/admin/users",
                             *          @SWG\Operation(
                             *              nickname="admin.users.read",
                             *              httpMethod="GET"
                             *          )
                             *      )
                             * )
                             */
                            'admin.users.read' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'get',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\User',
                                        'action' => 'readList',
                                    ),
                                ),
                            ),

                            /**
                             * Admin.Users.Create: POST (create a user)
                             * @SWG\Resource(
                             *      resourcePath="/admin",
                             *      @SWG\Api(
                             *          path="/admin/users",
                             *          @SWG\Operation(
                             *              nickname="admin.users.create",
                             *              httpMethod="POST"
                             *          )
                             *      )
                             * )
                             */
                            'admin.users.create' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'post,put',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\User',
                                        'action' => 'create',
                                    ),
                                ),
                            ),

                            // Admin.Users.Specific
                            'admin.users.specific' => array(
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => array(
                                    'route' => '/:user_id',
                                    'constraints' => array(
                                        'user_id' => '[0-9]+',
                                    ),
                                ),
                                'child_routes' => array(

                                    /**
                                     * Admin.Users.Specific.Read: GET (read a specific user)
                                     * @SWG\Resource(
                                     *      resourcePath="/admin",
                                     *      @SWG\Api(
                                     *          path="/admin/users/{userId}",
                                     *          @SWG\Operation(
                                     *              nickname="admin.users.specific.read",
                                     *              httpMethod="GET"
                                     *          )
                                     *      )
                                     * )
                                     */
                                    'admin.users.specific.read' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'get',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\User',
                                                'action' => 'read',
                                            ),
                                        ),
                                    ),

                                    //
                                    /**
                                     * Admin.Users.Specific.Update: POST (update a specific user)
                                     * @SWG\Resource(
                                     *      resourcePath="/admin",
                                     *      @SWG\Api(
                                     *          path="/admin/users/{userId}",
                                     *          @SWG\Operation(
                                     *              nickname="admin.users.specific.update",
                                     *              httpMethod="POST"
                                     *          )
                                     *      )
                                     * )
                                     */
                                    'admin.users.specific.update' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'post,put',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\User',
                                                'action' => 'update',
                                            ),
                                        ),
                                    ),

                                    /**
                                     * Admin.Users.Specific.Delete: DELETE (delete a specific user)
                                     * @SWG\Resource(
                                     *      resourcePath="/admin",
                                     *      @SWG\Api(
                                     *          path="/admin/users/{userId}",
                                     *          @SWG\Operation(
                                     *              nickname="admin.users.specific.delete",
                                     *              httpMethod="DELETE"
                                     *          )
                                     *      )
                                     * )
                                     */
                                    'admin.users.specific.delete' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'delete',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\User',
                                                'action' => 'delete',
                                            ),
                                        ),
                                    ),

                                ),
                            ),

                        ),
                    ),

                    // Admin.Clients
                    'admin.clients' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/clients',
                        ),
                        'child_routes' => array(

                            /**
                             * Admin.Clients.Read: GET (read list of clients)
                             * @SWG\Resource(
                             *      resourcePath="/admin",
                             *      @SWG\Api(
                             *          path="/admin/clients",
                             *          @SWG\Operation(
                             *              nickname="admin.clients.read",
                             *              httpMethod="GET"
                             *          )
                             *      )
                             * )
                             */
                            'admin.clients.read' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'get',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Client',
                                        'action' => 'readList',
                                    ),
                                ),
                            ),

                            /**
                             * Admin.Clients.Create: POST (create a client)
                             * @SWG\Resource(
                             *      resourcePath="/admin",
                             *      @SWG\Api(
                             *          path="/admin/clients",
                             *          @SWG\Operation(
                             *              nickname="admin.clients.create",
                             *              httpMethod="POST"
                             *          )
                             *      )
                             * )
                             */
                            'admin.clients.create' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'post,put',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Client',
                                        'action' => 'create',
                                    ),
                                ),
                            ),

                            // Admin.Clients.Specific
                            'admin.clients.specific' => array(
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => array(
                                    'route' => '/:client_id',
                                    'constraints' => array(
                                        'client_id' => '[0-9]+',
                                    ),
                                ),
                                'child_routes' => array(

                                    /**
                                     * Admin.Clients.Specific.Read: GET (read a specific client)
                                     * @SWG\Resource(
                                     *      resourcePath="/admin",
                                     *      @SWG\Api(
                                     *          path="/admin/clients/{clientId}",
                                     *          @SWG\Operation(
                                     *              nickname="admin.clients.specific.read",
                                     *              httpMethod="GET"
                                     *          )
                                     *      )
                                     * )
                                     */
                                    'admin.clients.specific.read' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'get',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\Client',
                                                'action' => 'read',
                                            ),
                                        ),
                                    ),

                                    /**
                                     * Admin.Clients.Specific.Update: POST (update a specific client)
                                     * @SWG\Resource(
                                     *      resourcePath="/admin",
                                     *      @SWG\Api(
                                     *          path="/admin/clients/{clientId}",
                                     *          @SWG\Operation(
                                     *              nickname="admin.clients.specific.update",
                                     *              httpMethod="POST"
                                     *          )
                                     *      )
                                     * )
                                     */
                                    'admin.clients.specific.update' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'post,put',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\Client',
                                                'action' => 'update',
                                            ),
                                        ),
                                    ),

                                    /**
                                     * Admin.Users.Specific.Delete: DELETE (delete a specific client)
                                     * @SWG\Resource(
                                     *      resourcePath="/admin",
                                     *      @SWG\Api(
                                     *          path="/admin/clients/{clientId}",
                                     *          @SWG\Operation(
                                     *              nickname="admin.clients.specific.delete",
                                     *              httpMethod="DELETE"
                                     *          )
                                     *      )
                                     * )
                                     */
                                    'admin.clients.specific.delete' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'delete',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\Client',
                                                'action' => 'delete',
                                            ),
                                        ),
                                    ),

                                    /**
                                     * Admin.Users.Specific.Duplicate: COPY (duplicate a specific client)
                                     * @SWG\Resource(
                                     *      resourcePath="/admin",
                                     *      @SWG\Api(
                                     *          path="/admin/clients/{clientId}/duplicate",
                                     *          @SWG\Operation(
                                     *              nickname="admin.clients.specific.duplicate",
                                     *              httpMethod="POST"
                                     *          )
                                     *      )
                                     * )
                                     */
                                    'admin.clients.specific.duplicate' => array(

                                        'type' => 'Zend\Mvc\Router\Http\Literal',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'route'=>'/duplicate',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\Client',
                                                'action' => 'duplicate',
                                            ),
                                        ),
                                    ),

                                ),
                            ),

                        ),
                    ),

                ),
            ),


            // End Routes
        ),
    ),


    /**
     * Services manager
     */
    'service_manager' => array(

        'invokables' => array(
            'Rbac' => 'ZfcRbac\Service\Rbac',
        ),
    ),


    /**
     * Translation manager. Delete me?
     */
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),


    /**
     * Invokable controllers list.
     * Maps the Controller Names (for routes) to Controller Objects.
     */
    'controllers' => array(
        'invokables' => array(
            'Preslog\Controller\Api' => 'Preslog\Controller\ApiController',
            'Preslog\Controller\Dashboard' => 'Preslog\Controller\DashboardController',
            'Preslog\Controller\Client' => 'Preslog\Controller\ClientController',
            'Preslog\Controller\User' => 'Preslog\Controller\UserController',
            'Preslog\Controller\Log' => 'Preslog\Controller\LogController',
            'Preslog\Controller\Search' => 'Preslog\Controller\SearchController',
        ),
    ),


    /**
     * View manager
     */
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/preslog/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
