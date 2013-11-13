<?php

/**
 * Swagger-PHP configuration
 */

Configure::write('swagger', array(

    /**
     * File paths that Swagger should read to discover documentation
     */
    'paths' => array(
        __DIR__ . '/../',     // All app files
    ),

));
