<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->default = array(
        'enabled' => true,
        'index' => 'Accueil',
        'sitemap'=>array('priority' => '0.5')
);
