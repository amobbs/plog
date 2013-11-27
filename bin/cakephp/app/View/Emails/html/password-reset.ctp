<?php $resetUrl = FULL_BASE_URL.'/?token='.$user['password-token'];?>

<h1>Password Reset</h1>

<p>Dear <?php echo $user['firstName'].' '.$user['lastName']; ?>,</p>

<p>To reset your password for MediaHub Preslog, follow the link below, where you will be able to specify a new password.</p>

<a href="<?php echo $resetUrl; ?>"><?php echo $resetUrl; ?></a>