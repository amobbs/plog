<?php

class DATABASE_CONFIG {
    public $default = array(
        'datasource' => 'Mongodb.MongodbSource',
        'persistent' => 'true',

        'host' => '192.168.4.125',
        'database' => 'preslog',
        'port' => 27017,

        'prefix' => '',

        'login' => 'root',
        'password' => 'root',

        'replicaset' => array(
            'host' => 'mongodb://root:root@192.168.4.125:27017',
            'options' => array('connect' => true,  'readPreference' => 'primary')
        ),

    );
}