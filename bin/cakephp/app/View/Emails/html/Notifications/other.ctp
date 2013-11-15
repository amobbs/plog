<?php

// Log Link URL
$logUrl = FULL_BASE_URL.'/logs/'.$slug;

?>
<p>A report has been added to the PresLog system.</p>

<p><a href="<?php echo $logUrl; ?>">View Log <?php echo $hrid; ?> on PresLog</a></p>

<table cellspacing="0" cellpadding="0">
<?php

$out = array();

foreach ($fields as $label=>$value)
{
    $out[] = '<tr style="border-bottom: 1px solid #aaa;">';
    $out[] = "\t".'<td style="border-bottom: 1px solid #ccc; padding:2px 20px 2px 0px; font-weight:bold;">'.htmlspecialchars($label)."</td>";
    $out[] = "\t".'<td style="border-bottom: 1px solid #ccc; padding:2px;">'.htmlspecialchars($value)."</td>";
    $out[] = '</tr>';
}

echo implode("\n", $out);

?>
</table>