<?php
/**
 * Schema behavior.
 *
 * Adds functionality specific to MongoDB/schemaless dbs in order to imply a schema
 * Restricts data to the specified schema (per the MongoDB datasource) but also provides further assistance
 * with conversion between MongoIds, MongoDate and any other fields that are needed.
 *
 * PHP version 5
 *
 * Copyright (c) 2010, 4mation Technologies
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright       Copyright (c) 2010, 4mation Technologies
 * @link            www.4mation.com.au
 * @package         mongodb
 * @subpackage      mongodb.models.behaviors
 * @since           v 1.0 (1-Oct-2013)
 * @license         http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * SchemaBehavior class
 *
 * @uses            ModelBehavior
 * @package         mongodb
 * @subpackage      mongodb.models.behaviors
 */
class SchemaBehavior extends ModelBehavior
{

    public $name = 'Schema';

    /**
     * Setup this behaviours
     * - Find the primary key in the Schema and set it on the Model
     */
    public function setup(Model $Model, $settings = array())
    {
        // Default primary key is _id
        $key = '_id';
        // Try to find the Primary Key in the schema
        foreach ($Model->mongoSchema as $fieldName => $fieldOptions) {
            // Does it have primary key?
            if (isset($fieldOptions['primary'])) {
                $key = $fieldName;
                break;
            }
        }
        // Primary key set!
        $Model->primaryKey = $key;
    }
}

