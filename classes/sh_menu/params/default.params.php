<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->default = array(
    'renderFiles' => array(
        0 => 'oneLineMenu',
        1 => 'oneLineMenu',
        2 => 'oneLineMenu',
        3 => 'oneLineMenu'),
    'availableLinks'=>array(
        'Page d\'accueil' => '/index.php',
        'Page de contact' => '/contact.php'
        ),
        'sitemap'=>array('priority' => '0.7',
                        'frequency' => 'weekly'));
