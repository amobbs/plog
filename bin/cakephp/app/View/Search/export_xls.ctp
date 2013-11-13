<?php
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . date('Y-m-d') .'.xls"');

?>
<table>

    <?php

    $fieldNames = array();

    $logsArray = array();

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
    ?>

    <tr>

        <?php
        foreach($fieldNames as $name)
        {
            ?>
            <td><?php echo $name; ?></td>
        <?php
        }
        ?>

    </tr>

    <?php

    foreach($logArray as $log)
    {
        ?>
        <tr>
            <?php
            foreach($log as $value)
            {
                ?>
                <td><?php echo $value; ?></td>
            <?php
            }
            ?>
        </tr>
    <?php
    }
    ?>

</table>