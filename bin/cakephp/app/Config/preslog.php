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

    /**
     * regular expressions
     */
    'regex' => array(
        'duration' => '/^(([0-9]{1,3})([h|H]))?(([0-9]{1,3})([m|M]))?(([0-9]{1,3})([s|S]))?$/', //30h20m5s or 20s or 30m20s or 30h10s or 30h or 20m
    )
));


/**
 * Configuration options for dashboard exports
 */
Configure::write('Preslog.export.exec', array(

    //location of executable and scripts to perform export
    'phantomjs' => 'D:\ProgramFiles\phantomjs-1.9.2-windows/phantomjs.exe',
    'highchartsExport.js' => 'D:\git\highcharts.com\exporting-server\phantomjs\highcharts-convert.js',
));

/**
 * config for printed layout of dashboard export
 */
Configure::write('Preslog.export.layout', array(
    //details for log tables
    'titleColWidth' => 2160, // in twips
    'detailColWidth' => 7200, // in twips
    'titleColor' => '1F497D', //in hex - dark blue
    'cellBorder' => 10, //in twips
    'cellBorderColor' => 'ffffff' //in hex
));
