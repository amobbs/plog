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
class SchemaBehavior extends ModelBehavior {

    public $name = 'Schema';

    /**
     * Setup this behaviours
     * - Find the primary key in the Schema and set it on the Model
     */
    public function setup( Model $Model, $settings = array() )
    {
        // Default primary key is _id
        $key = '_id';

        // Try to find the Primary Key in the schema
        foreach ($Model->mongoSchema as $fieldName=>$fieldOptions)
        {
            // Does it have primary key?
            if ( isset($fieldOptions['primary']) )
            {
                $key = $fieldName;
                break;
            }
        }

        // Primary key set!
        $Model->primaryKey = $key;
    }


    /**
     * @param   array     $options
     * @return  boolean
     */
    public function beforeSave( Model $model, $options = array() )
    {
        // Convert data types for save
        $this->extractModel( $this->data, $model->mongoSchema);

        return true;
    }


    /**
     * After Find
     * Convert the
     * @param   array           $results
     * @param   bool            $primary
     * @return  array|mixed
     */
    public function afterFind( Model $model, $results, $primary = false )
    {
        foreach ($results as &$result)
        {
            // Process the returned doc against the schema doc
            $this->hydrateModel( $result[ $model->name ], $model->mongoSchema );
        }

        return $results;
    }


    /**
     * From Database to Model
     */
    public function hydrateModel( array &$doc, array $schema )
    {
        // Check for numeric array keys
        $numericKeys = array_filter(array_keys($doc), 'is_int');

        // If an array, recuse each one on the schema type.
        if (sizeof($numericKeys) == sizeof($doc))
        {
            foreach ($doc as &$docItem)
            {
                $this->hydrateModel($docItem, $schema);
            }

            return;
        }

        // Convert schema
        foreach ( $schema as $fieldKey=>$fieldOptions )
        {
            // Null types are ignores. These are dynamic schemas
            if ($fieldOptions === null)
            {
                continue;
            }

            // No type and not null implies a sub-document
            // Recurse into the sub-document
            if (!isset($fieldOptions['type']))
            {
                $this->hydrateModel( $doc[$fieldKey], $fieldOptions );
            }

            // Skip non-type fields
            if ( !isset($fieldOptions['mongoType']) )
            {
                continue;
            }

            // MongoId
            if ($fieldOptions['mongoType'] == 'MongoId')
            {
                $doc[$fieldKey] = (string) $doc[$fieldKey];
            }

            // MongoDate
            if ($fieldOptions['mongoType'] == 'MongoDate')
            {
                $doc[$fieldKey] = date('Y-M-d h:i:s', $doc[$fieldKey]->sec);
            }
        }
    }


    /**
     * From Model to Database
     */
    public function extractModel( &$doc, $schema )
    {
        // Check for numeric array keys
        $numericKeys = array_filter(array_keys($doc), 'is_int');

        // If an array, recuse each one on the schema type.
        if (sizeof($numericKeys) == sizeof($doc))
        {
            foreach ($doc as &$docItem)
            {
                $this->extractModel($docItem, $schema);
            }

            return;
        }

        // Convert schema
        foreach ( $schema as $fieldKey=>$fieldOptions )
        {
            // Null types are ignores. These are dynamic schemas
            if ($fieldOptions === null)
            {
                continue;
            }

            // No type and not null implies a sub-document
            // Recurse into the sub-document
            if (!isset($fieldOptions['type']))
            {
                $this->extractModel( $doc[$fieldKey], $fieldOptions );
            }

            // Skip non-type fields
            if ( !isset($fieldOptions['mongoType']) )
            {
                continue;
            }

            // MongoId
            if ($fieldOptions['mongoType'] == 'MongoId')
            {
                $doc[$fieldKey] = new MongoId( $doc[$fieldKey] );
            }

            // MongoDate
            if ($fieldOptions['mongoType'] == 'MongoDate')
            {
                $doc[$fieldKey] = new MongoDate( $doc[$fieldKey] );
            }
        }
    }


}

