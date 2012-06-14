<?php

/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if( !defined( 'SH_MARKER' ) )
    header( 'location: directCallForbidden.php' );

$this->default = array(
    'list' => array( ),
    'renderFiles' => array(
        0 => dirname( __FILE__ ) . '/../oneLineMenu.php',
        1 => dirname( __FILE__ ) . '/../oneLineMenu.php' ),
    'availableLinks' => array(
        'Page d\'accueil' => '/index.php',
        'Page de contact' => '/contact.php'
    ),
    'sitemap' => array( 'priority' => '0.5',
        'frequency' => 'weekly' )
);
