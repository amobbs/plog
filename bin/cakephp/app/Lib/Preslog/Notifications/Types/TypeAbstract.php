<?php

namespace Preslog\Notifications\Types;


/**
 * Preslog Notification: Type Abstract
 * Extend this to create different notification types.
 */
abstract class TypeAbstract
{

    /**
     * @var string      Unique for this notification type
     */
    protected $key = '';

    /**
     * @var string      Friendly name for user reference
     */
    protected $name = '';

    /**
     * @var array       Settings for the different methods that are available.
     */
    protected $settings = array();


    /**
     * Check if this notification has the given method type
     * @param   string      $method
     * @return  bool
     */
    public function hasMethod($method)
    {
        return array_key_exists($method, $this->methods);
    }


    /**
     * Get Key for referencing this notification type
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }


    /**
     * Get the friendly name for this notification
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Check Criteria of the log to assure this Notification type is relevant
     * @param   array       $log
     * @return  bool
     */
    public function checkCriteria( $log )
    {
        return false;
    }


    /**
     * Fetch settings for the specified type
     * @param   string      $type
     * @return  array|bool
     */
    public function getSettings( $type )
    {
        $settings = (isset($this->settings[ $type ]) ? $this->settings[ $type ] : false);
        return $settings;
    }

}