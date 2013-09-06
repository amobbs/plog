<?php

/**
 * Custom Auth-ACL configuration
 */

Configure::write('auth-acl', array(

    // Anonymous role for unauthorised users
    'anonymousRole' => 'guest',

    // Super user role - can do anything
    'superUser' => 'super-admin',


    /**
     * User permissions sequence
     */
    'permissions'=>array(
        'super-admin'   => array('guest', 'dashboards', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete', 'admin', 'user-manager', 'client-manager', 'edit-preset-dashboards'),
        'admin'         => array('guest', 'dashboards', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete', 'admin', 'user-manager', 'client-manager'),
        'supervisor'    => array('guest', 'dashboards', 'log-create', 'dashboard-export-reports', 'log-accountability', 'log-delete'),
        'operator'      => array('guest', 'dashboards', 'log-create'),
        'engineer'      => array('guest', 'dashboards', 'single-client', 'comment-only'),
        'client'        => array('guest', 'dashboards', 'single-client', 'dashboard-export-reports', 'comment-only'),
        'guest'         => array('guest'),
    ),


    /**
     * Controller-based permissions
     */
    'routes'=>array(
        array('controller'=>'Users', 'action'=>'*', 'permissions'=>array('guest')),

    )

));
