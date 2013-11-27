<?php

/**
 * Show headings
 */
if ($headings)
{
    echo "<table>\n";

    // Headers
    echo "<tr>\n";
    foreach($headings as $heading)
    {
        echo "\t<th>".htmlspecialchars($heading['label'])."</th>\n";
    }
    echo "</tr>\n";

    // No logs on heading? Empty export!
    if ( !sizeof($logs) )
    {
        echo "<tr><td>No data to export.</td></tr>";
    }
}

// Output array
$outputLogs = array();

// Logs
foreach($logs as $log)
{
    $sortedLog = array();

    // Change field order to match headings
    foreach ($log['attributes'] as $field)
    {
        // Put the values to key sorted fields.
        $sortedLog[ $map[ $field['title'] ] ] = $field['value'];
    }

    // Output
    echo "<tr>\n";
    foreach($map as $fieldKey=>$fieldPos)
    {
        // Populate the value. Empty if no value.
        $value = ( isset( $sortedLog[ $fieldPos ] ) ?  $sortedLog[ $fieldPos ] : '' );
        echo "\t<td>".htmlspecialchars($value)."</td>\n";
    }
    echo "</tr>\n";
}