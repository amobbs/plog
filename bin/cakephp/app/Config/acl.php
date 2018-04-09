<?php

/**
 * Custom Auth-ACL configuration
 */

Configure::write('auth-acl', array(

    /** Anonymous role for unauthorised users */
    'anonymousRole' => 'guest',

    /**
     * User roles sequence
     * Note: Some permissions are restrictive, like "comment-only" and "single-client".
     *       "Hidden" prevents the user type being selectable in the User Admin
     */
    'roles'=>array(
        'super-admin'   => array(
            'name'=>'Super Admin',
            'hidden'=>true,
            'permissions'=>array('guest', 'user', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete', 'admin', 'user-manager', 'client-manager', 'edit-preset-dashboards', 'super-admin', 'dashboard-create', 'dashboard-custom'),
        ),

        'admin'         => array(
            'name'=>'Administrator',
            'permissions'=>array('guest', 'user', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete', 'admin', 'user-manager', 'client-manager', 'dashboard-create', 'dashboard-custom'),
        ),

        'supervisor'    => array(
            'name'=>'Supervisor',
            'permissions'=>array('guest', 'user', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete', 'dashboard-create', 'dashboard-custom'),
        ),

        'operator'      => array(
            'name'=>'Operator',
            'permissions'=>array('guest', 'user', 'log-create'),
        ),

        'engineer'      => array(
            'name'=>'Engineer',
            'permissions'=>array('guest', 'user', 'comment-only'),
        ),

        'client'        => array(
            'name'=>'Client',
            'permissions'=>array('guest', 'user', 'single-client', 'dashboard-export-reports', 'comment-only', 'dashboard-create', 'dashboard-custom'),
        ),

        'client-logs-only'        => array(
            'name'=>'Client (Logs Only)',
            'permissions'=>array('guest', 'user', 'single-client', 'comment-only'),
        ),

        'client-create-logs' => array(
	        'name'=>'Client (Log Creator)',
	        'permissions'=>array('guest', 'user', 'log-create', 'log-delete', 'single-client'),
        ),
        'guest'         => array(
            'name'=>'Guest',
            'hidden'=>true,
            'permissions'=>array('guest'),
        ),
    ),


    /**
     * Controller-based access permissions
     */
    'routes'=>array(

        // Public Routes
        array('controller'=>'Users',    'action'=>'login',              'permissions'=>array('guest')),
        array('controller'=>'Users',    'action'=>'resetPassword',      'permissions'=>array('guest')),
        array('controller'=>'Users',    'action'=>'resetPasswordEmail', 'permissions'=>array('guest')),
        array('controller'=>'Users',    'action'=>'logout',             'permissions'=>array('guest')),
        array('controller'=>'Docs',     'action'=>'*',                  'permissions'=>array('guest')),

        // User Access - Accessible to user-manager only
        array('controller'=>'Users',    'action'=>'adminList',          'permissions'=>array('user-manager')),
        array('controller'=>'Users',    'action'=>'adminRead',          'permissions'=>array('user-manager')),
        array('controller'=>'Users',    'action'=>'adminEdit',          'permissions'=>array('user-manager')),
        array('controller'=>'Users',    'action'=>'adminEditOptions',   'permissions'=>array('user-manager')),
        array('controller'=>'Users',    'action'=>'adminDelete',        'permissions'=>array('user-manager')),

        // User "My*" Access - Accessible to users
        array('controller'=>'Users',    'action'=>'*',                  'permissions'=>array('user')),

        // Client Access - Accessible ot client-manager only
        array('controller'=>'Clients',  'action'=>'*',                  'permissions'=>array('client-manager')),

        // Log Access - Everyone
        array('controller'=>'Logs',     'action'=>'read',               'permissions'=>array('user')),
        array('controller'=>'Logs',     'action'=>'options',            'permissions'=>array('user')),
        array('controller'=>'Logs',     'action'=>'edit',               'permissions'=>array('user')),
        array('controller'=>'Logs',     'action'=>'delete',             'permissions'=>array('log-delete')),

        // Dashboards and Widgets - Everyone
        array('controller'=>'Dashboards',   'action'=>'*',              'permissions'=>array('user')),
        array('controller'=>'Search',       'action'=>'*',              'permissions'=>array('user')),
        array('controller'=>'Pages',        'action'=>'*',              'permissions'=>array('user')),

        // Dash create/Edot
        array('controller'=>'Dashboards',   'action'=>'editDashboard',  'permissions'=>array('dashboard-create')),
    )

));
