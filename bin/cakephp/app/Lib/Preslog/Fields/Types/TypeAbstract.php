<?php

namespace Preslog\Fields\Types;


/**
 * Preslog Field Types: Type Abstract
 * Extend this to create different field types for use with Preslog.
 */
abstract class TypeAbstract
{

    /**
     * @var string      Unique for this notification type
     */
    protected $alias = '';


    /**
     * @var string      Human-readable Name of the field
     */
    protected $name = '';


    /**
     * @var string      Friendly name for user reference
     */
    protected $description = '';


    /**
     * Fetch a list of properties for this field, or just the one specified.
     * @param   string|null  $property
     * @return  array
     */
    public function getProperties( $property=null )
    {
        $out = $this->getPropertyList();

        if (!empty($property))
        {
            if (!isset( $out[ $property ] ))
            {
                trigger_error("Requested property '{$property}' does not exist on field '{$this->alias}'", E_USER_WARNING);
            }

            return $out[ $property ];
        }

        return $out;
    }


    /**
     * Fetch all properties
     * @return  array
     */
    protected function getPropertyList()
    {
        return array(
            'alias'         =>$this->alias,
            'name'          =>$this->name,
            'description'   =>$this->description,
        );
    }

}