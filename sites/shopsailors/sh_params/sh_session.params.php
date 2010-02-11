<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->values = array(
        'allowedAdmins' => array(
            'admin'=>MD5('bRiCeazerty')
        )
    );
