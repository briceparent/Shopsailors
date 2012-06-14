<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->default = array(
    'bank'=>array(
        'class'=>'cm_etransactions',
    ),

    'cm_etransactions'=>array(
    ),

);
