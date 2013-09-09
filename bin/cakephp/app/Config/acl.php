<?php

/**
 * Custom Auth-ACL configuration
 */

Configure::write('auth-acl', array(

    /** Anonymous role for unauthorised users */
    'anonymousRole' => 'guest',

    /**
     * User permissions sequence
     * Note: Some permissions are restrictive, like "comment-only" and "single-client".
     */
    'permissions'=>array(
        'super-admin'   => array('guest', 'user', 'dashboards', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete', 'admin', 'user-manager', 'client-manager', 'edit-preset-dashboards'),
        'admin'         => array('guest', 'user', 'dashboards', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete', 'admin', 'user-manager', 'client-manager'),
        'supervisor'    => array('guest', 'user', 'dashboards', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete'),
        'operator'      => array('guest', 'user', 'dashboards', 'log-create'),
        'engineer'      => array('guest', 'user', 'dashboards', 'single-client', 'comment-only'),
        'client'        => array('guest', 'user', 'dashboards', 'single-client', 'dashboard-export-reports', 'comment-only'),
        'guest'         => array('guest'),
    ),


    /**
     * Controller-based access permissions
     * TODO: Finish populating this list
     */
    'routes'=>array(

        // Public routes
        array('controller'=>'Users', 'action'=>'login', 'permissions'=>array('guest')),
        array('controller'=>'Users', 'action'=>'logout', 'permissions'=>array('user')),
        array('controller'=>'Docs', 'action'=>'*', 'permissions'=>array('guest')),

        // User Management
        array('controller'=>'Users', 'action'=>'adminList', 'permissions'=>array('user-manager')),
        array('controller'=>'Users', 'action'=>'adminEdit', 'permissions'=>array('user-manager')),
        array('controller'=>'Users', 'action'=>'adminEditOptions', 'permissions'=>array('user-manager')),

        // Client Management


        // TODO - REMOVE DEBUG - Super-Admin can do anything
        array('controller'=>'*', 'action'=>'*', 'permissions'=>array('super-admin')),
    )

));
