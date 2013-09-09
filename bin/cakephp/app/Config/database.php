<?php

class DATABASE_CONFIG {
    public $default = array(
        'datasource' => 'Mongodb.MongodbSource',
        'persistent' => 'true',

        'host' => 'localhost',
        'database' => 'preslog',
        'port' => 27017,

        'prefix' => '',

        'login' => 'root',
        'password' => 'root',

        'replicaset' => array(
            'host' => 'mongodb://root:root@localhost:27021',
          'options' => array('replicaSet' => '4mation')
        ),
    );
}