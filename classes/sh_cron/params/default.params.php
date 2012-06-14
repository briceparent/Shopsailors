<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->default = array(
    'launchers' => array(
        '127.0.0.1',
        '192.168.*',
        '88.191.116.222'
    )
);
