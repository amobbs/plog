<?php

    $fieldNames = array();

    $logsArray = array();

echo "<table>\n";

if (empty($logs))
{
    ?>
    <tr><td>No Logs Found</td></tr>
    </table>
    <?php
    exit();
}

foreach ($logs as $log)
{
    $logEl = array();
    foreach($log['attributes'] as $attr)
    {

        if ( ! isset($fieldNames[$attr['title']]) )
        {
            $fieldNames[$attr['title']] = $attr['title'];
        }

        $logEl[$attr['title']] = $attr['value'];
    }

    $logArray[] = $logEl;
}

// Headers
echo "<tr>\n";
foreach($fieldNames as $name)
{
    echo "\t<th>".htmlspecialchars($name)."</th>\n";
}
echo "</tr>\n";

// Logs
foreach($logArray as $log)
{
    echo "<tr>\n";
    foreach($log as $value)
    {
        echo "\t<td>".htmlspecialchars($value)."</td>\n";
    }
    echo "</tr>\n";
}

echo "</table>\n";