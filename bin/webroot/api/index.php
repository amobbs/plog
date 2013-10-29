<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */

chdir(dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'cakephp'.DIRECTORY_SEPARATOR.'app');

define('WEBROOT_DIR', dirname(__FILE__));

// Begin
require 'index.php';

