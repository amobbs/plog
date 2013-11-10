<?php
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . date('Y-m-d') .'.xls"');

?>
<table>

<?php

$fieldNames = array();
//    'hrid' => 'Log ID',
//    'created' => 'Created',
//    'modified' => 'Modified',
//);

$logsArray = array();

foreach ($logs as $log)
{
    $logEl = array();
   // $logEl['hrid'] = $log['hrid'];
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