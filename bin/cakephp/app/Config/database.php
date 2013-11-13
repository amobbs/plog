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

    /** @var array Development DB */
    public $development = array(
        'datasource'    => 'Mongodb.MongodbSource',
        'persistent'    => 'true',
        'prefix'        => '',
        'database'      => 'preslog',
        'slaveok'       => true,
        'replicaset'    => array(
            'host'          => 'mongodb://root:root@192.168.4.125:27017',
            'options'       => array(
                'connect'           => true,
                'readPreference'    => 'primary'
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

        // Failover testing
        if (false !== stripos($_SERVER['SERVER_NAME'], 'local.preslog'))
        {
            $this->default = $this->failoverTest;
        }
    }


}