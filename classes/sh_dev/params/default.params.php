<?php

/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if( !defined( 'SH_MARKER' ) )
    header( 'location: directCallForbidden.php' );

$this->default = array(
    'codeCoverage' =>
    array(
        'activated' => '',
        'path' => '/tmp',
    ),
    'debug_path' =>
    array(
        'path' => '../dev',
    ),
    'trace' =>
    array(
        'activated' => '',
    ),
    'errors' =>
    array(
        'e_all' => 'checked',
        'e_strict' => '',
        'e_warning' => '',
        'e_notice' => 'checked',
    ),
    'debug' =>
    array(
        'activated' => '',
    )
);
