<?php
/**
 * SMS Notification Template
 * Custom format following from Preslog pre-2013
 *
 * @var     LogEntity   $log
 */

use Preslog\Logs\Entities\LogEntity;



// Subject
$out = array();
$out[] = 'High Severity: '.$hrid;

// URL
$logUrl = FULL_BASE_URL.'/logs/'.$slug;
$out[] = $logUrl;

// Description
$field = $log->getFieldByName('what');
$out[] = ($field ? current($field->convertToFields()) : '');


// Program Name
$field = $log->getFieldByName('program');
$out[] = ($field ? current($field->convertToFields()) : '');


// Date/Time
$field = $log->getFieldByName('datetime');
$created = $field->convertToFields();
$out[] = ((isset($created['DateTime']) && !empty($created['DateTime'])) ? $created['DateTime'] : '');


// Duration
$field = $log->getFieldByName('duration');
$out[] = 'Dur: '.($field ? current($field->convertToFields()) : '');


// Attribute Fields
$fields = $log->getFlattenedAttributes();
if ( is_array($fields) && sizeof($fields))
{
    foreach ($fields as $fKey=>$field)
    {
        $out[] = $fKey.': '.$field;
    }
}


// Severity
$field = $log->getFieldByName('severity');
$out[] = 'Sev: '.($field ? current($field->convertToFields()) : '');


// Output
echo implode("\n", $out);