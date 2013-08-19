<?php
/**
 * ZfcRbac Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */

use User\View\UnauthorizedStrategyFactory;

$settings = array(
    /**
     * The default role that is used if no role is found from the
     * role provider.
     */
    'anonymousRole' => 'guest',

    /**
     * Flag: enable or disable the routing firewall.
     */
    'firewallRoute' => true,

    /**
     * Flag: enable or disable the controller firewall.
     */
    'firewallController' => false,

    /**
     * flag: enable or disable the use of lazy-loading providers.
     */
    'enableLazyProviders' => true,

    'firewalls' => array(

        /**
         * Route-based access restrictions
         */
        'ZfcRbac\Firewall\Route' => array(

            // API Documentation can be accessed by anyone


            // Public (Guest)
            array('route' => 'api',         'permissions' => 'guest'),      // API UI
            array('route' => 'api.docs',    'permissions' => 'guest'), // API Docs

            array('route' => 'login',       'roles' => 'guest'),          // Login / Homepage
            array('route' => 'logout',      'roles' => 'guest'),         // Logout

            // Standard Users
            array('route' => 'users.my-profile/*',          'permissions' => 'user'),         // User: My-Profile
            array('route' => 'users.my-notifications/*',    'permissions' => 'user'),         // User: My-Notifications

            // Logs
            array('route' => 'logs/*',                      'permissions' => 'user'),         // Logs
            array('route' => 'logs/create',                 'permissions' => 'log-create'),   // Logs: Create
            array('route' => 'logs/specific/delete',        'permissions' => 'log-delete'),   // Logs: Delete

            // Dashboards
            array('route' => 'dashboards/*',                'permissions' => 'dashboards'),   // Dashboard Access
            array('route' => 'dashboards/dashboards.specific/dashboards.export', 'permissions' => 'dashboard-export-reports'),   // export
            array('route' => 'widgets/*',                   'permissions' => 'dashboards'),   // Dashboard Access

            // Search
            array('route' => 'search/*',                    'permissions' => 'user'),         // Search

            // Admin
            array('route' => 'user/register',   'roles' => 'guest'),
            array('route' => 'admin/admin.user/*',         'permissions' => 'user-manager'),
            array('route' => 'admin/admin.client/*',       'permissions' => 'client-manager'),

            // Block everything else
            array('route' => '/*', 'roles' => 'admin'),
        ),

        /**
         * Controller-based access restrictions
         *
        'ZfcRbac\Firewall\Controller' => array(
            array('controller' => 'Preslog/Controller/Index', 'roles' => 'guest'),
            array('controller' => 'User/Controller/User'),
        ),*/
    ),

    'providers' => array(

        /**
         * Rule permissions inheritance
         * Each role specified below can specify a Parent Role.
         * Parents will inherit permissions (see permissions section) from their subordinates.
         */
        'ZfcRbac\Provider\Generic\Role\InMemory' => array(
            'roles' => array(
                'super-admin'   => array(),
                'admin'         => array('super-admin'),
                'supervisor'    => array('admin'),
                'operator'      => array('supervisor'),
                'engineer'      => array(),
                'client'        => array(),
                'user'          => array('client', 'operator', 'engineer', 'supervisor', 'admin', 'super-admin'),
                'guest'         => array('user'),
            ),
        ),

        /**
         * Permissions can be assigned to roles. Permissions will be checked in code.
         * Permissions are inherited (see above).
         *
         * Permission types and what they control:
         * - guest:                         Guest sections only
         * - user:                          User is logged in
         * - dashboards:                    Can view Custom and Preset Dashboards
         * - single-client:                 Can only view the one client they're assigned to
         * - comment-only:                  May only comment on Logs during an update
         * - dashboards-export-reports:     May export Dashboards as reports
         * - accountability-fields:         May see/edit the Accountability fields in Logs
         * - log-create:                    May create new Logs
         * - log-delete:                    May delete Logs
         * - user-manager:                  Can access the User Manager
         * - client-manager:                Can access the Client Manager
         * - admin:                         User gets the Admin controls
         * - edit-preset-dashboards:        May edit the Preset dashboards.
         */
        'ZfcRbac\Provider\Generic\Permission\InMemory' => array(
            'permissions' => array(
                'super-admin'   => array('edit-preset-dashboards'),
                'admin'         => array('admin', 'user-manager', 'client-manager'),
                'supervisor'    => array('dashboards', 'dashboard-export-reports', 'accountability-fields', 'log-delete'),
                'operator'      => array('dashboards', 'log-create'),
                'engineer'      => array('dashboards', 'single-client', 'comment-only'),
                'client'        => array('dashboards', 'single-client', 'dashboard-export-reports', 'comment-only'),
                'user'          => array('user'),
                'guest'         => array('guest'),
            )
        ),
    ),

    /**
     * Set the identity provider to use. The identity provider must be retrievable from the
     * service locator and must implement \ZfcRbac\Identity\IdentityInterface.
     */
    'identity_provider' => 'user_role',
);

/**
 * You do not need to edit below this line
 */
return array(
    'zfcrbac' => $settings,

    'service_manager' => array(
        'factories' => array(

            // Override the default UnauthorisedViewStrategyFactory with our own
            'ZfcRbac\View\UnauthorizedStrategy' =>  new \User\View\UnauthorizedStrategyFactory
        ),
    ),
);
