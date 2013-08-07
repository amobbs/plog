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
        'ZfcRbac\Firewall\Route' => array(

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
        ),
    ),

    'providers' => array(
        // These
        'ZfcRbac\Provider\Generic\Role\InMemory' => array(
            'roles' => array(
                'admin',
                'salesperson' => array('admin', 'office_admin', 'estimator', 'approver'),
                'office_admin' => array('admin'),
                'estimator' => array('office_admin', 'admin'),
                'approver' => array('admin'),
                'guest' => array('salesperson', 'office_admin', 'estimator', 'approver', 'admin'),
            ),
        ),

        // Generic rules go here
        'ZfcRbac\Provider\Generic\Permission\InMemory' => array(
            'permissions' => array(
                'admin' => array('admin', 'tender-create', 'store-version', 'edit-version', 'generate-version', 'delete-version', 'delete-deleted-items'),
                'salesperson' => array('tender-create'),
                'office_admin' => array('tender-create', 'edit-version', 'generate-version'),
                'estimator' => array('tender-create', 'generate-version', 'edit-version'),
                'approver' => array('store-version', 'generate-version', 'edit-version'),
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
