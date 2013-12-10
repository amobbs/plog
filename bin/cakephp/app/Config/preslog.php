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
        'severity-one'      => new \Preslog\Notifications\Types\SeverityOne,
        'severity-two'      => new \Preslog\Notifications\Types\SeverityTwo,
        'severity-three'    => new \Preslog\Notifications\Types\SeverityThree,
        'impact-affected'   => new \Preslog\Notifications\Types\ImpactAffected,
        'other'            => new \Preslog\Notifications\Types\Others,
    ),


    /**
     * List of Field Types for Logs
     */
    'Fields' => array(
        'loginfo'               => new \Preslog\Logs\FieldTypes\Loginfo,
        'checkbox'              => new \Preslog\Logs\FieldTypes\Checkbox,
        'datetime'              => new \Preslog\Logs\FieldTypes\Datetime,
        'duration'              => new \Preslog\Logs\FieldTypes\Duration,
        'select'                => new \Preslog\Logs\FieldTypes\Select,
        'select-accountability' => new \Preslog\Logs\FieldTypes\SelectAccountability,
        'select-impact'         => new \Preslog\Logs\FieldTypes\SelectImpact,
        'select-severity'       => new \Preslog\Logs\FieldTypes\SelectSeverity,
        'textarea'              => new \Preslog\Logs\FieldTypes\Textarea,
        'textsmall'             => new \Preslog\Logs\FieldTypes\Textsmall,
        'textbig'               => new \Preslog\Logs\FieldTypes\Textbig,
    ),


    /**
     * List of preset values
     */
    'Quantities' => array(
        'BHPM' => 730, //broadcast hours per month - per network
        'decimalPlacesForPercentages' => 4,
    ),

    'Primetime' => array(
        'start' => 17,
        'end' => 0,
    ),

    /**
     * SMS Notification API
     */
    'SmsService' => array(
        'username'  => "curtisd",
        'password'  => "test",
        'from'      => "MediaHub",
        'address'   => 'https://smsgw.exetel.com.au/sendsms/api_sms.php?username=%s&password=%s&messagetype=Text&sender=%s',
    ),


    /**
     * Some debug vars
     */
    'Debug' => array(
        'email' => 'dave@4mation.com.au',
        'sms' => '0468904530',  // Don't call me.
    ),

    /**
     * regular expressions
     */
    'regex' => array(
        'duration' => '/^(([0-9]{1,3})([h|H]))?(([0-9]{1,3})([m|M]))?(([0-9]{1,3})([s|S]))?$/', //30h20m5s or 20s or 30m20s or 30h10s or 30h or 20m
        'logid' => '/^([A-Z]{1,6})_#(\d+)$/i', //upto 6 letters followed by 1 or more numbers, eg: ABC1234
    ),

    /**
     * Dashboard IDs
     */
    'Dashboards' => array(
        'unqualified' => '5260bf91ad7cc5782600002a'
    ),

    /**
     * Client Placeholder
     */
    'Client' => array(
        'logoPlaceholder' => '/assets/clients/logo-placeholder.gif',
    ),

));


/**
 * Configuration options for dashboard exports
 */

Configure::write('Preslog.export.exec', array(

    //location of executable and scripts to perform export
    'phantomjs' => '/usr/local/phantomjs-1.9.2-linux-x86_64/bin/phantomjs',
    'highchartsExport.js' => '/usr/local/highcharts.com/phantomjs/highcharts-convert.js',
));

// Development override
if ('development' == APPLICATION_ENV)
{
    Configure::write('Preslog.export.exec', array(

        //location of executable and scripts to perform export
        'phantomjs' => 'D:\tmp\phantomjs\phantomjs-1.9.1-windows\phantomjs.exe',
        'highchartsExport.js' => 'D:\Development\local.preslog\web\vendor\highcharts.com\exporting-server\phantomjs\highcharts-convert.js',
    ));
}



/**
 * config for printed layout of dashboard export
 */
Configure::write('Preslog.export.layout', array(
    //details for log tables
    'textSize' => 10,
    'titleColWidth' => 2160, // in twips
    'detailColWidth' => 7200, // in twips
    'titleColor' => '1F497D', //in hex - dark blue
    'cellBorder' => 10, //in twips
    'cellBorderColor' => 'ffffff', //in hex
    'red' => 'C00000', //red in hex (taken from sample KPI report)
    'brown' => '984806', //used for Non-primetime, i guess it is brown-ish (taken from sampel KPI report)
));
