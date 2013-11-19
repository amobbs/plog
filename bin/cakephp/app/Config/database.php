<?php

class DATABASE_CONFIG {

    /** @var array Production DB */
    public $default = array(
        'datasource'    => 'Mongodb.MongodbSource',
        'persistent'    => 'true',
        'prefix'        => '',
        'slaveok'       => true,
        'database'      => 'preslog',
        'replicaset'    => array(
            'host'          => 'mongodb://preslog:cup$shiny&hat!purple@192.168.0.18:27017,192.168.0.19:27017',
            'options'       => array(
                'connect'           => true,
                'readPreference'    => \MongoClient::RP_PRIMARY_PREFERRED,
                'replicaSet'        => 'mediahub-preslog'
            ),
        ),
    );


    /** @var array Staging DB */
    public $staging = array(
        'datasource'    => 'Mongodb.MongodbSource',
        'persistent'    => 'true',
        'prefix'        => '',
        'slaveok'       => true,
        'database'      => 'preslog-test',
        'replicaset'    => array(
            'host'          => 'mongodb://preslog:cup$shiny&hat!purple@192.168.0.18:27017,192.168.0.19:27017',
            'options'       => array(
                'connect'           => true,
                'readPreference'    => \MongoClient::RP_PRIMARY_PREFERRED,
                'replicaSet'        => 'mediahub-preslog'
            ),
        ),
    );


    /** @var array Development DB */
    public $development = array(
        'datasource'    => 'Mongodb.MongodbSource',
        'persistent'    => 'true',
        'prefix'        => '',
        'database'      => 'preslog',
        'replicaset'    => array(
            'host'          => 'mongodb://root:root@192.168.4.125:27017',
            'options'       => array(
                'connect'           => true,
                'readPreference'    => \MongoClient::RP_PRIMARY_PREFERRED,
            ),
        ),
    );


    /** @var array Development DB */
    public $failoverTest = array(
        'datasource'    => 'Mongodb.MongodbSource',
        'persistent'    => 'true',
        'prefix'        => '',
        'slaveok'       => false,
        'database'      => 'preslog',
        'replicaset'    => array(
            'host'          => 'mongodb://preslog:cup$shiny&hat!purple@192.168.4.125:27018,192.168.4.127:27017',
            'options'       => array(
                'connect'           => true,
                'readPreference'    => \MongoClient::RP_PRIMARY_PREFERRED,
                'replicaSet'        => '4mation'),
        ),
    );

    /**
     * Environment switcher
     */
    public function __construct()
    {
        // Development
        if ('development' == APPLICATION_ENV)
        {
            $this->default = $this->development;
        }

        // Staging
        if ('staging' == APPLICATION_ENV)
        {
            $this->default = $this->staging;
        }

    }


}