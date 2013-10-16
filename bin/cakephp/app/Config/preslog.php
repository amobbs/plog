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
        'loginfo'           => new \Preslog\Fields\Types\Loginfo,
        'datetime'          => new \Preslog\Fields\Types\Datetime,
        'duration'          => new \Preslog\Fields\Types\Duration,
        'select'            => new \Preslog\Fields\Types\Select,
        'select-impact'     => new \Preslog\Fields\Types\SelectImpact,
        'select-severity'   => new \Preslog\Fields\Types\SelectSeverity,
        'textarea'          => new \Preslog\Fields\Types\Textarea,
        'textsmall'         => new \Preslog\Fields\Types\Textsmall,
        'textbig'           => new \Preslog\Fields\Types\Textbig,
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
    'titleColWidth' => 1440, // in twips
    'detailColWidth' => 7200, // in twips
    'titleColor' => '1F497D', //in hex - dark blue
    'cellBorder' => 10, //in twips
    'cellBorderColor' => 'ffffff' //in hex
));