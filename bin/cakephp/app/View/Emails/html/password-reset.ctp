<h1>Password Reset</h1>

<p>Hi <?php echo $user['firstName'].' '.$user['lastName']; ?>,</p>

<p>To reset your password for MediaHub Preslog, follow the link below, where you will be able to specify a new password.</p>

<?php echo '/?token='.$user['password-token']; ?>

<sub>This is an automated email. Please do not reply.</sub>