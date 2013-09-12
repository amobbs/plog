<?php

/**
 * Client Model
 */

App::uses('AppModel', 'Model');

class Client extends AppModel
{
    public $name = "Client";


    /**
     * @var array   Schema definition for this document
     */
    public $mongoSchema = array(
        '_id'           => array('type' => 'string', 'length'=>40, 'primary' => true),
        'name'          => array('type' => 'string', 'length'=>255),
        'shortName'     => array('type' => 'string', 'length'=>4),
        'contact'       => array('type' => 'text'),
        'logPrefix'     => array('type' => 'string', 'length'=>4),
        'activationDate'=> array('type' => 'datetime'),
        'format'        => array('type' => null),

        'created'       => array('type' => 'datetime'),
        'modified'      => array('type' => 'datetime'),
    );




}