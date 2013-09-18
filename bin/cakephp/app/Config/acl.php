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
            'permissions'=>array('guest', 'user', 'dashboards', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete', 'admin', 'user-manager', 'client-manager', 'edit-preset-dashboards', 'super-admin'),
        ),

        'admin'         => array(
            'name'=>'Administrator',
            'permissions'=>array('guest', 'user', 'dashboards', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete', 'admin', 'user-manager', 'client-manager'),
        ),

        'supervisor'    => array(
            'name'=>'Supervisor',
            'permissions'=>array('guest', 'user', 'dashboards', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete'),
        ),

        'operator'      => array(
            'name'=>'Operator',
            'permissions'=>array('guest', 'user', 'dashboards', 'log-create'),
        ),

        'engineer'      => array(
            'name'=>'Engineer',
            'permissions'=>array('guest', 'user', 'dashboards', 'single-client', 'comment-only'),
        ),

        'client'        => array(
            'name'=>'Client',
            'permissions'=>array('guest', 'user', 'dashboards', 'single-client', 'dashboard-export-reports', 'comment-only'),
        ),

        'guest'         => array(
            'name'=>'Guest',
            'hidden'=>true,
            'permissions'=>array('guest'),
        ),
    ),


    /**
     * Controller-based access permissions
     * TODO: Finish populating this list
     */
    'routes'=>array(

        // Public routes
        array('controller'=>'Users',    'action'=>'debugTask',  'permissions'=>array('guest')),     // TODO: DELETE ME
        array('controller'=>'Import',   'action'=>'runImport',  'permissions'=>array('guest')),     // TODO: DELETE ME
        array('controller'=>'Users',    'action'=>'login',      'permissions'=>array('guest')),
        array('controller'=>'Users',    'action'=>'logout',     'permissions'=>array('user')),
        array('controller'=>'Docs',     'action'=>'*',          'permissions'=>array('guest')),

        // User Management - Accessible to user-manager only
        array('controller'=>'Users',    'action'=>'adminList',          'permissions'=>array('user-manager')),
        array('controller'=>'Users',    'action'=>'adminEdit',          'permissions'=>array('user-manager')),
        array('controller'=>'Users',    'action'=>'adminEditOptions',   'permissions'=>array('user-manager')),

        // Client Management - Accessible ot client-manager only
        array('controller'=>'Clients',    'action'=>'adminList',          'permissions'=>array('user-manager')),
        array('controller'=>'Clients',    'action'=>'adminEdit',          'permissions'=>array('user-manager')),
        array('controller'=>'Clients',    'action'=>'adminEditOptions',   'permissions'=>array('user-manager')),



        // TODO - DEBUG - DELETE ME - Super-Admin can do anything
        array('controller'=>'*', 'action'=>'*', 'permissions'=>array('super-admin')),
    )

));
