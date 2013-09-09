<?php

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'cakephp'.DIRECTORY_SEPARATOR.'app');

// Begin
require 'index.php';
