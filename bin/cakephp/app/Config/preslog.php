<?php


/**
 * Preslog system-specific configuration
 */

Configure::write('Preslog', array(

    /**
     * List of Notification classes to load for notification purposes.
     * Should follow namespace formatting.
     */
    'Notifications' => array(
        'severity-one'  => new \Preslog\Notifications\Types\SeverityOne,
        'severity-two'  => new \Preslog\Notifications\Types\SeverityTwo,
        'others'        => new \Preslog\Notifications\Types\Others,
    ),


    /**
     * List of Field Types for Logs
     */
    'Fields' => array(
        'loginfo'           => new \Preslog\Logs\FieldTypes\Loginfo,
        'datetime'          => new \Preslog\Logs\FieldTypes\Datetime,
        'duration'          => new \Preslog\Logs\FieldTypes\Duration,
        'select'            => new \Preslog\Logs\FieldTypes\Select,
        'select-impact'     => new \Preslog\Logs\FieldTypes\SelectImpact,
        'select-severity'   => new \Preslog\Logs\FieldTypes\SelectSeverity,
        'textarea'          => new \Preslog\Logs\FieldTypes\Textarea,
        'textsmall'         => new \Preslog\Logs\FieldTypes\Textsmall,
        'textbig'           => new \Preslog\Logs\FieldTypes\Textbig,
    ),


    /**
     * SMS Notification API
     */
    'SmsService' => array(
        'username'  => "curtisd",
        'password'  => "test",
        'from'      => "MediaHub",
        'address'   => 'https://smsgw.exetel.com.au/sendsms/api_sms.php?username=%s&Pwd=%s&messagetype=Text&sender=%s',
    ),


    /**
     * Some debug vars
     */
    'Debug' => array(
        'email' => 'dave@4mation.com.au',
    ),

));


/**
 * Highcharts Configuration options
 */
Configure::write(array('highcharts_export_server' => 'http://192.168.4.49:8080/highcharts-export-web/'));
