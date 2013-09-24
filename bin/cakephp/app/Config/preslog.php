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

));


/**
 * Highcharts Configuration options
 */
Configure::write(array('highcharts_export_server' => 'http://192.168.4.49:8080/highcharts-export-web/'));
