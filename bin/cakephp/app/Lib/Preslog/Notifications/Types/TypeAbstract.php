<?php

namespace Preslog\Notifications\Types;

use Preslog\Logs\Entities\LogEntity;

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
     * @var array       Recipients (users), grouped by method type
     */
    protected $recipients;

    /**
     * @var LogEntity   Log Entity
     */
    protected $log;


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
     * Has the method?
     * @param   string  $method     Method to look for
     * @return  bool                True if method exists
     */
    public function hasMethod( $method )
    {
        return (isset($this->settings[$method]));
    }


    /**
     * Check Criteria of the log to assure this Notification type is relevant
     * @return  bool
     */
    public function checkCriteria()
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


    /**
     * Add recipient
     * - Check the user wants this type
     * - Check the method they're interested
     * - Store to separate arrays depending on type specified
     * @param   array   $user
     */
    public function addRecipient( $user )
    {
        // Sort into interest groups
        foreach ($user['notifications']['methods'] as $method=>$enabled)
        {
            if ($enabled !== false)
            {
                $this->recipients[ $method ][] = $user;
            }
        }
    }


    /**
     * Fetch recipients for the specified method
     * @param   string  $method     Notification method
     * @return  array               List of users attached
     */
    public function getRecipients($method)
    {
        return (isset($this->recipients[ $method ]) ? $this->recipients[ $method ] : array());
    }


    /**
     * Save a link to the log details
     * @param $log
     */
    public function setLog( &$log )
    {
        $this->log = &$log;
    }


    /**
     * Get the log
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }


    /**
     * Fetch the subject line for this notification type
     * @return  string      Subject
     */
    public function getTemplateData()
    {
        $out = array();
        $out['fields'] = $this->log->toDisplay();
        $out['subject'] = 'I am a Teapot.';
        $out['clientShortName'] = $this->log->getClient()->data['shortName'];

        return $out;
    }

}