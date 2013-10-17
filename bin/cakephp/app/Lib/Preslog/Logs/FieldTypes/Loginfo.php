<?php

namespace Preslog\Logs\FieldTypes;

use Preslog\Logs\FieldTypes\FieldTypeAbstract;

/**
 * Preslog Field Type: Loginfo
 * Handles log information for general logs. Used just to control this items position in the log.
 */
class Loginfo extends FieldTypeAbstract
{

    protected $alias = 'loginfo';
    protected $name = 'Log Information';
    protected $description = 'A block that contains general log information.';

    protected $aggregationDetails = array(
        'hour' => array(
            'fieldName' => 'created',
            '$project' => array(
                'hour' => array('operation' => '$hour', 'data' => '$fields.data.created'),
            ),
            '$group' => array(),
        ),
        'day' => array(
            'fieldName' => 'created',
            '$project' => array(
                'day' => array('operation' => '$dayOfMonth', 'data' => '$fields.data.created'),
                'month' => array('operation' => '$month', 'data' => '$fields.data.created'),
            ),
            '$group' => array()
        ),
        'month' => array(
            'fieldName' => 'created',
            '$project' => array(
                'month' => array('operation' => '$month', 'data' => '$fields.data.created'),
                'year' => array('operation' => '$year', 'data' => '$fields.data.created'),
            ),
            '$group' => array()
        ),
    );

    protected $mongoSchema = array(
        'created'           => array('type' => 'datetime', 'mongoType'=>'mongoDate'),
        'modified'          => array('type' => 'datetime', 'mongoType'=>'mongoDate'),
        'created_user_id'   => array('type' => 'string', 'length'=>24, 'mongoType'=>'mongoId'),
        'modified_user_id'  => array('type' => 'string', 'length'=>24, 'mongoType'=>'mongoId'),
        'version'           => array('type' => 'int'),
    );

    protected $mongoClientSchema = array();

}
