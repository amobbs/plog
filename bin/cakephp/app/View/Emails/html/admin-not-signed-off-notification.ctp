<h1>Logs Not Currently Signed Off For <?php echo $niceDate ?></h1>

<p><a href="<?php echo FULL_BASE_URL ?>/dashboard/<?php echo $dashboardId ?>">Click here</a> to view the dashboard.</p>
<table style="width: 40%; border-collapse: collapse; border: 1px solid #a9a9a9;">
    <tr>
        <td style="border: 1px solid #d3d3d3;"><strong>Name</strong></td>
        <td style="border: 1px solid #d3d3d3;"><strong>Asset ID</strong></td>
    </tr>
    <?php foreach($logs as $log): ?>
    <tr>
        <td style="border: 1px solid #d3d3d3;"><?php echo $log['Log']['Program Name'] ?></td>
        <td style="border: 1px solid #d3d3d3;"><?php echo $log['Log']['Asset ID'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>