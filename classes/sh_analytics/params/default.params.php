<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->default = array(
        'showAddress' => true,
        'showPhone' => true,
        'showMail' => false,
        'sendMail' => true
);
