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

return array(

    /**
     * Routes
     * WARNING: If you update the routes below, update the documentation in the attached controllers!
     * Documentation follows formatting of swagger-php (http://zircote.com/swagger-php/)
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
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/docs',
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

                    // Users.My-Profile: GET (read user)
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

                    // Users.My-Profile: POST (update user)
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

                    // User.My-Notifications: GET (read notifications)
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

                    // User.My-Notifications: POST (update notifications)
                    'users.my-notifications.update' => array(
                        'type' => 'Zend\Mvc\Router\Http\Method',
                        'may_terminate' => true,
                        'options' => array(
                            'verb' => 'get',
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

                    // Logs: GET (read log)
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

                    // Logs: POST (create/update log)
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

                    // Logs: DELETE (delete log)
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

                    // Dashboards: GET (read dashboards)
                    'dashboards.read' => array(
                        'type' => 'Zend\Mvc\Router\Http\Method',
                        'may_terminate' => true,
                        'options' => array(
                            'verb' => 'get',
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\Dashboard',
                                'action' => 'getDashboardList'
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

                            // Dashboards.Specific: GET (read dashboard)
                            'dashboards.specific.read' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'get',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Dashboard',
                                        'action' => 'readDashboard'
                                    ),
                                ),
                            ),

                            // Dashboards.Specific: POST (update dashboard)
                            'dashboards.specific.update' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'post,put',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Dashboard',
                                        'action' => 'updateDashboard'
                                    ),
                                ),
                            ),

                            // Dashboards.Specific: DELETE (delete dashboard)
                            'dashboards.specific.delete' => array(
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

                            // Dashboard.Specific.Widgets
                            'dashboards.specific.widgets' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => array(
                                    'route' => '/widgets',
                                ),
                                'child_routes' => array(

                                    // Dashboard.Specific.Widgets.Create: POST (create widget on dashboard)
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

                                            // Dashboards.Specific.Widgets.Specific.Read: GET (read existing widget)
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

                                            // Dashboards.Specific.Widgets.Specific.Update: POST (update existing widget)
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

                                            // Dashboards.Specific.Widgets.Specific.Delete: DELETE (remove existing widget)
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

                                            // Dashboards.Specific.Widgets.Specific.Export: GET (export widget logdata to xls)
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

                            // Dashboards.specific.export-report
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

                    // Dashboard.Create: POST (create new dashboard)
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
                                        'action' => 'createDashboard',
                                    ),
                                ),
                            ),
                        ),
                    ),


                ),
            ),


            // Widgets
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

                    // Widgets.Options
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


            // Search: GET (fetch logs based on query)
            'search' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/search',
                    'defaults' => array(
                        'controller' => 'Preslog\Controller\Dashboard',
                        'action' => 'search',
                    ),
                ),
                'child_routes' => array(

                    // Search.Export: GET (fetch and export logs based on query)
                    'search.export' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'may_terminate' => true,
                        'options' => array(
                            'route' => '/export-xls',
                            'defaults' => array(
                                'controller' => 'Preslog\Controller\Dashboard',
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

                            // Search.Wizard.Params: GET (generate params for Query Builder)
                            'search.wizard.params' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'may_terminate' => true,
                                'options' => array(
                                    'route' => '/params',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Dashboard',
                                        'action' => 'searchWizardParams',
                                    ),
                                ),
                            ),

                            // Search.Wizard.Translate: GET (Translate between SQL and JQL)
                            'search.wizard.translate' => array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'may_terminate' => true,
                                'options' => array(
                                    'route' => '/translate',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Dashboard',
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

                            // Admin.Users.Read: GET (read list of users)
                            'admin.users.read' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'get',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Users',
                                        'action' => 'readUserList',
                                    ),
                                ),
                            ),

                            // Admin.Users.Create: POST (create a user)
                            'admin.users.create' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'post,put',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Users',
                                        'action' => 'createUser',
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

                                    // Admin.Users.Specific.Read: GET (read a specific user)
                                    'admin.users.specific.read' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'get',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\Users',
                                                'action' => 'readUser',
                                            ),
                                        ),
                                    ),

                                    // Admin.Users.Specific.Update: POST (update a specific user)
                                    'admin.users.specific.update' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'post,put',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\Users',
                                                'action' => 'updateUser',
                                            ),
                                        ),
                                    ),

                                    // Admin.Users.Specific.Delete: DELETE (delete a specific user)
                                    'admin.users.specific.delete' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'delete',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\Users',
                                                'action' => 'deleteUser',
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

                            // Admin.Clients.Read: GET (read list of clients)
                            'admin.clients.read' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'get',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Clients',
                                        'action' => 'readClientList',
                                    ),
                                ),
                            ),

                            // Admin.Clients.Create: POST (create a client)
                            'admin.clients.create' => array(
                                'type' => 'Zend\Mvc\Router\Http\Method',
                                'may_terminate' => true,
                                'options' => array(
                                    'verb' => 'post,put',
                                    'defaults' => array(
                                        'controller' => 'Preslog\Controller\Clients',
                                        'action' => 'createClient',
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

                                    // Admin.Clients.Specific.Read: GET (read a specific client)
                                    'admin.clients.specific.read' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'get',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\Clients',
                                                'action' => 'readClient',
                                            ),
                                        ),
                                    ),

                                    // Admin.Clients.Specific.Update: POST (update a specific client)
                                    'admin.clients.specific.update' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'post,put',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\Clients',
                                                'action' => 'updateClient',
                                            ),
                                        ),
                                    ),

                                    // Admin.Users.Specific.Delete: DELETE (delete a specific client)
                                    'admin.clients.specific.delete' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Method',
                                        'may_terminate' => true,
                                        'options' => array(
                                            'verb' => 'delete',
                                            'defaults' => array(
                                                'controller' => 'Preslog\Controller\Clients',
                                                'action' => 'deleteClient',
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
            //'Preslog\Service\ApiDocs' => 'Preslog\Service\ApiDocs'
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
