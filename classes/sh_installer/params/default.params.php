<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
header('location: directCallForbidden.php');

$this->default = array(
    'class'=>'content',
    'action'=>'show',
    'id'=>1,
    'link'=>'1.php',
    'sitemap'=>array('priority'=>1),
);
