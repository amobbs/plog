<?php

// Log Link URL
$logUrl = FULL_BASE_URL.'/logs/'.$slug;

?>
A "<?php echo $severity; ?>" report has been added to the PresLog system.

View Log <?php echo $hrid; ?> on PresLog: <?php echo $logUrl; ?>

<?php

$out = array();

foreach ($fields as $label=>$value)
{
    $out[] = str_pad(htmlspecialchars($label).':', 25, ' ', STR_PAD_RIGHT).' '.htmlspecialchars($value);
}

echo implode("\n", $out);

?>
