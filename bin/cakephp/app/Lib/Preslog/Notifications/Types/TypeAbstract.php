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
        $this->recipients[] = $user;
    }


    /**
     * Fetch recipients for the specified method
     * @return  array               List of users attached
     */
    public function getRecipients()
    {
        return $this->recipients;
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
     * Fetch the template data for Email
     * @return  string      Subject
     */
    public function getEmailTemplateData()
    {
        $out = array();
        $out['fields'] = $this->log->toDisplay('email');
        $out['subject'] = 'I am a Teapot.';
        $out['clientShortName'] = $this->log->getClient()->data['shortName'];

        return $out;
    }


    /**
     * Fetch the template data for Sms
     * @return  string      Subject
     */
    public function getSmsTemplateData()
    {
        $out = array();
        $out['log'] = $this->log;
        $out['clientShortName'] = $this->log->getClient()->data['shortName'];

        return $out;
    }


    /**
     * Fetch this notification types priority
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

}