<?php $resetUrl = FULL_BASE_URL.'?token='.$user['password-token'];?>

Password Reset

Hi <?php echo $user['firstName'].' '.$user['lastName']; ?>,

To reset your password for MediaHub Preslog, follow the link below, where you will be able to specify a new password.

<?php echo $resetUrl; ?>