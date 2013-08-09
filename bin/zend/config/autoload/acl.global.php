<?php
/**
 * ZfcRbac Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
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
    'firewallController' => true,

    /**
     * Set the view template to use on a 403 error.
     */
    'template' => 'error/403',

    /**
     * flag: enable or disable the use of lazy-loading providers.
     */
    'enableLazyProviders' => true,

    'firewalls' => array(

        /**
         * Route-based access restrictions
         */
        'ZfcRbac\Firewall\Route' => array(

            array('route' => 'home', 'permissions' => 'guest'),
            //array('route' => 'login', 'roles' => 'guest'),
            //array('route' => 'logout', 'roles' => 'guest'),
            //array('route' => 'user/register', 'roles' => 'guest'),
            //array('route' => 'admin/*', 'roles' => 'admin'),

            // Block everything
            array('route' => '/*', 'roles' => 'admin'),

            /*

            // Pdf
            array('route' => 'pdf/*', 'roles' => 'salesperson'),

            // Admin
            array('route' => 'admin/*', 'roles' => 'admin'),

            // Tenders
            array('route' => 'tender/list', 'roles' => 'salesperson'),
            array('route' => 'tender/fetch/*', 'roles' => 'salesperson'),
            array('route' => 'tender/create/*', 'roles' => array('salesperson', 'office_admin', 'estimator')),
            array('route' => 'tender/variation', 'roles' => 'estimator'),
            array('route' => 'tender/view/*', 'roles' => 'salesperson'),
            array('route' => 'tender/edit/*', 'roles' => 'estimator'),
            array('route' => 'tender/store/*', 'roles' => 'approver'),
            array('route' => 'tender/directive/*', 'roles' => 'salesperson'),
            array('route' => 'tender/comment/*', 'roles' => 'salesperson'),
            array('route' => 'tender/delete/*', 'roles' => 'admin'),
            array('route' => 'client', 'roles' => 'salesperson'),
            array('route' => 'attachment', 'roles' => 'salesperson'),
            array('route' => 'ajax-find-house', 'roles' => 'salesperson'),

            // Vendors
            array('route' => 'DkcwdZf2Munee', 'roles' => 'guest'),
            array('route' => 'StaticPages', 'roles' => 'salesperson'),

            // ZfcUser
            array('route' => 'zfcuser/login', 'roles' => 'guest'),
            array('route' => 'zfcuser/logout', 'roles' => 'guest'),
            array('route' => 'zfcuser/lost-password', 'roles' => 'guest'),
            array('route' => 'zfcuser/reset-password', 'roles' => 'guest'),
            array('route' => 'zfcuser/invalid', 'roles' => 'guest'),
            array('route' => 'login', 'roles' => 'guest'),
            array('route' => 'logout', 'roles' => 'guest'),
//
//            // Home
            array('route' => 'home', 'roles' => 'guest'),
            array('route' => 'dashboard', 'roles' => 'salesperson'),
            array('route' => '/*', 'roles' => 'admin'),
*/
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
         * Each role specified below can specify a Parent Role.  Parents will inherit permissions from their subordinates.
         */
        'ZfcRbac\Provider\Generic\Role\InMemory' => array(
            'roles' => array(
                'super-admin'   => array(),
                'admin'         => array('super-admin'),
                'supervisor'    => array('admin'),
                'operator'      => array('supervisor'),
                'engineer'      => array(),
                'client'        => array(),
                'guest'         => array('client', 'operator', 'engineer', 'supervisor', 'admin'),
            ),
        ),

        /**
         * Permissions can be assigned to roles. Permissions will be checked in code.
         * Permissions are inherited (see above).
         */
        'ZfcRbac\Provider\Generic\Permission\InMemory' => array(
            'permissions' => array(
                'super-admin'   => array('manage-preset-dashboards'),
                'admin'         => array('admin'),
                'supervisor'    => array('dashboards', 'dashboard-export-reports', 'accountability-fields', 'log-delete'),
                'operator'      => array(),
                'engineer'      => array('dashboards', 'single-client', 'comment-only'),
                'client'        => array('dashboards', 'single-client', 'dashboard-export-reports', 'comment-only'),
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
);
