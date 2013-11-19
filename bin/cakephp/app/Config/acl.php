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
            'permissions'=>array('guest', 'user', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete', 'admin', 'user-manager', 'client-manager', 'edit-preset-dashboards', 'super-admin'),
        ),

        'admin'         => array(
            'name'=>'Administrator',
            'permissions'=>array('guest', 'user', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete', 'admin', 'user-manager', 'client-manager'),
        ),

        'supervisor'    => array(
            'name'=>'Supervisor',
            'permissions'=>array('guest', 'user', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete'),
        ),

        'operator'      => array(
            'name'=>'Operator',
            'permissions'=>array('guest', 'user', 'log-create'),
        ),

        'engineer'      => array(
            'name'=>'Engineer',
            'permissions'=>array('guest', 'user', 'single-client', 'comment-only'),
        ),

        'client'        => array(
            'name'=>'Client',
            'permissions'=>array('guest', 'user', 'single-client', 'dashboard-export-reports', 'comment-only'),
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

        // Super Routes (TODO: DELETEME)
        array('controller'=>'Users',    'action'=>'debugTask',          'permissions'=>array('super-admin')),
        array('controller'=>'Logs',     'action'=>'notificationtest',   'permissions'=>array('guest')),
        array('controller'=>'Import',   'action'=>'runImport',          'permissions'=>array('admin')),

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
    )

));
